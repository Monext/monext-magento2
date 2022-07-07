<?php

namespace Monext\Payline\Logger\Handler;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Filesystem\DriverInterface;
use Magento\Framework\Logger\Handler\Base;
use Monext\Payline\Helper\Constants as HelperConstants;
use Monext\Payline\Model\System\Config\Source\DebugLevel;
use Monolog\Logger;

class Debug extends Base
{
    /**
     * @var string
     */
    protected $fileName = '/var/log/payline.log';

    /**
     * @var int
     */
    protected $loggerType = Logger::ERROR;
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;



    /**
     * @param DriverInterface $filesystem
     * @param string $filePath
     * @param string $fileName
     * @throws \Exception
     */
    public function __construct(
        DriverInterface $filesystem,
        ScopeConfigInterface $scopeConfig,
        $filePath = null,
        $fileName = null
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->loggerType = $this->scopeConfig->getValue(HelperConstants::CONFIG_PATH_PAYLINE_GENERAL_DEBUG, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if (!$this->loggerType || (int)$this->loggerType<Logger::DEBUG) {
            $this->loggerType = Logger::ERROR;
        }

        parent::__construct(
            $filesystem,
            $filePath,
            $fileName
        );
    }
}
