<?php


namespace ModMage\ChangeStatusOrder\Helper;

use \Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Helper\AbstractHelper;

class Config extends AbstractHelper
{
    protected $scopeConfig;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    public function isEnable()
    {
        return $this->scopeConfig->getValue('change_order_status/general/enable', ScopeInterface::SCOPE_WEBSITE);
    }


    public function getPeriod()
    {
        return $this->scopeConfig->getValue('change_order_status/general/period', ScopeInterface::SCOPE_WEBSITE);
    }

    public function getWaitingTime()
    {
        return $this->scopeConfig->getValue('change_order_status/general/waiting_time', ScopeInterface::SCOPE_WEBSITE);
    }

    public function sendInvoice()
    {
        return $this->scopeConfig->getValue('change_order_status/general/send_invoice', ScopeInterface::SCOPE_WEBSITE);
    }

}
