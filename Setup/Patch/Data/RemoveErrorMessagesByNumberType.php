<?php

namespace Monext\Payline\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\App\Config\ConfigResource\ConfigInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class RemoveErrorMessagesByNumberType implements DataPatchInterface
{

    protected ModuleDataSetupInterface $moduleDataSetup;
    protected ConfigInterface $config;

    public function __construct(ModuleDataSetupInterface $moduleDataSetup, ConfigInterface $config)
    {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->config = $config;
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }

    public function apply()
    {
        $this->moduleDataSetup->startSetup();

        $this->config->deleteConfig('payline/general/user_error_message_type1');
        $this->config->deleteConfig('payline/general/user_error_message_type2');
        $this->config->deleteConfig('payline/general/user_error_message_type3');
        $this->config->deleteConfig('payline/general/user_error_message_type4');

        $this->moduleDataSetup->endSetup();
    }
}
