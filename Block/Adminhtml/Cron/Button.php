<?php

namespace ModMage\ChangeStatusOrder\Block\Adminhtml\Cron;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Backend\Block\Template\Context;

class Button extends Field
{
    protected $_template = 'ModMage_ChangeStatusOrder::system/config/button.phtml';

    public function __construct(Context $context, array $data = [])
    {
        parent::__construct($context, $data);
    }

    public function render(AbstractElement $element)
    {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
    }

    protected function _getElementHtml(AbstractElement $element)
    {
        return $this->_toHtml();
    }

    public function getButtonHtml()
    {
        $button = $this->getLayout()->createBlock('Magento\Backend\Block\Widget\Button')->setData(['id' => 'run_cron', 'label' => __('Run cron'),]);
        return $button->toHtml();
    }


    public function getAjaxUrl()
    {
        return $this->getUrl('changestatus/cron/changestatus', ['form_key' => $this->getFormKey()]);
    }
}
