<?php

namespace ModMage\ChangeStatusOrder\Model;

use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Sales\Model\Service\InvoiceService;
use Magento\Framework\DB\Transaction;
use Magento\Sales\Model\Order\Email\Sender\InvoiceSender;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Convert\Order as ConvertFactory;
use Magento\Framework\App\Helper\Context;
use ModMage\ChangeStatusOrder\Helper\Config;

class ChangeStatus
{
    protected CollectionFactory $salesOrderCollectionFactory;
    protected DateTime $dateTime;
    protected Order $order;
    protected \Psr\Log\LoggerInterface $logger;
    protected InvoiceService $invoiceService;
    protected Transaction $transaction;
    protected InvoiceSender $invoiceSender;
    protected ConvertFactory $convert;
    protected Config $config;

    public function __construct(
        Context           $context,
        CollectionFactory $salesOrderCollectionFactory,
        DateTime          $dateTime,
        Order             $order,
        InvoiceService    $invoiceService,
        InvoiceSender     $invoiceSender,
        Transaction       $transaction,
        ConvertFactory    $convert,
        Config            $config

    )
    {
        $this->logger = $context->getLogger();
        $this->salesOrderCollectionFactory = $salesOrderCollectionFactory;
        $this->dateTime = $dateTime;
        $this->order = $order;
        $this->invoiceService = $invoiceService;
        $this->transaction = $transaction;
        $this->invoiceSender = $invoiceSender;
        $this->convert = $convert;
        $this->config = $config;
    }

    public
    function changeStatus()
    {

        if (!$this->config->isEnable()) {
            return;
        }
        $currentDateTime = $this->dateTime->gmtDate();
        $configPeriod = '-' . $this->config->getPeriod() . 'hours';
        $periodForCollection = date('Y-m-d H:i:s', strtotime($configPeriod, strtotime($currentDateTime)));
        $orderCollection = $this->salesOrderCollectionFactory->create()
            ->addFieldToFilter('created_at', ['gteq' => $periodForCollection]);

        foreach ($orderCollection as $order) {
            $orderStatus = $order->getStatus();
            if ($orderStatus != "complete" && $orderStatus != "canceled") {
                try {
                    $time = $order->getCreatedAt();
                    $diffTime = time() - strtotime($time);
                    $waitingTime = $this->config->getWaitingTime() * 3600;
                    if ($diffTime > $waitingTime) {
                        $this->createInvoice($order);
                        $this->createShipment($order);
                    }
                } catch (\Exception $e) {
                    $this->logger->error($e);
                }
            }
        }
    }

    public
    function createInvoice($order)
    {
        if ($order->canInvoice()) {
            try {
                $invoice = $this->invoiceService->prepareInvoice($order);
                $invoice->register();
                $invoice->save();

                $transactionSave = $this->transaction
                    ->addObject($invoice)
                    ->addObject($invoice->getOrder());

                $transactionSave->save();
            } catch (\Exception $e) {
                $this->logger->error($e);
            }

            if ($this->config->sendInvoice()) {
                $this->invoiceSender->send($invoice);
                $order->addCommentToStatusHistory(
                    __('Notified customer about invoice creation #%1.', $invoice->getId())
                )->setIsCustomerNotified(true)->save();
            }
        }
    }

    public
    function createShipment($order)
    {
        if (!$order->canShip()) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('You can\'t create the Shipment.'));
        }
        try {
            $shipment = $this->convert->toShipment($order);
            foreach ($order->getAllItems() as $orderItem) {
                if (!$orderItem->getQtyToShip() || $orderItem->getIsVirtual()) {
                    continue;
                }
                $qtyShipped = $orderItem->getQtyToShip();
                $shipmentItem = $this->convert->itemToShipmentItem($orderItem)->setQty($qtyShipped);
                $shipment->addItem($shipmentItem);
            }

            $shipment->register();
            $shipment->getOrder()->setIsInProcess(true);
            $shipment->getExtensionAttributes()->setSourceCode('default');
            $shipment->save();
            $shipment->getOrder()->save();
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $objectManager->create('Magento\Shipping\Model\ShipmentNotifier')
                ->notify($shipment);

            $order->addCommentToStatusHistory(
                __('Notified customer about shipment creation #%1.', $shipment->getId())
            )->setIsCustomerNotified(true)->save();
        } catch (\Exception $e) {
            $this->logger->error($e);
        }
    }
}

