<?php

namespace ModMage\ChangeStatusOrder\Cron;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Module\Manager;
use ModMage\ChangeStatusOrder\Model\ChangeStatus;

class Run
{
    private $moduleManager;

    public function __construct(Manager $moduleManager)
    {
        $this->moduleManager = $moduleManager;
    }

    public function execute()
    {
        $moduleManager = $this->moduleManager;

        if ($moduleManager->isEnabled('ModMage_ChangeStatusOrder')) {
            $change = ObjectManager::getInstance()->get(ChangeStatus::class);
            $change->changeStatus();
        }
    }
}
