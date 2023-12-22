<?php

namespace ModMage\ChangeStatusOrder\Controller\Adminhtml\Cron;

use ModMage\ChangeStatusOrder\Model\ChangeStatus as Change;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\State;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;

class ChangeStatus extends Action implements HttpPostActionInterface
{
    protected $state;
    protected $change;

    public function __construct(
        Context $context,
        State   $state,
        Change  $change
    )
    {
        parent::__construct($context);
        $this->state = $state;
        $this->change = $change;
    }

    public function execute()
    {
        $response = ['success' => false];

        try {
            $this->change->changeStatus();
            $response['success'] = true;
        } catch (LocalizedException $e) {
            $response['error'] = $e->getMessage();
        }

        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData($response);
        return $resultJson;
    }
}
