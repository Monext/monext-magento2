<?php

namespace Monext\Payline\PaylineApi;

use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Module\ModuleListInterface;
use Magento\Framework\App\ProductMetadata;
use Magento\Framework\App\Filesystem\DirectoryList;
use Monext\Payline\Helper\Constants as HelperConstants;
use Monext\Payline\PaylineApi\PaylineSDKFactory;
use Monext\Payline\PaylineApi\Request\DoCapture as RequestDoCapture;
use Monext\Payline\PaylineApi\Request\DoVoid as RequestDoVoid;
use Monext\Payline\PaylineApi\Request\DoRefund as RequestDoRefund;
use Monext\Payline\PaylineApi\Request\DoWebPayment as RequestDoWebPayment;
use Monext\Payline\PaylineApi\Request\GetMerchantSettings as RequestGetMerchantSettings;
use Monext\Payline\PaylineApi\Request\GetPaymentRecord as RequestGetPaymentRecord;
use Monext\Payline\PaylineApi\Request\GetWebPaymentDetails as RequestGetWebPaymentDetails;
use Monext\Payline\PaylineApi\Request\ManageWebWallet as RequestManageWebWallet;
use Monext\Payline\PaylineApi\Response\DoCapture as ResponseDoCapture;
use Monext\Payline\PaylineApi\Response\DoCaptureFactory as ResponseDoCaptureFactory;
use Monext\Payline\PaylineApi\Response\DoVoidFactory as ResponseDoVoidFactory;
use Monext\Payline\PaylineApi\Response\DoRefundFactory as ResponseDoRefundFactory;
use Monext\Payline\PaylineApi\Response\DoWebPayment as ResponseDoWebPayment;
use Monext\Payline\PaylineApi\Response\DoWebPaymentFactory as ResponseDoWebPaymentFactory;
use Monext\Payline\PaylineApi\Response\GetMerchantSettings as ResponseGetMerchantSettings;
use Monext\Payline\PaylineApi\Response\GetMerchantSettingsFactory as ResponseGetMerchantSettingsFactory;
use Monext\Payline\PaylineApi\Response\GetPaymentRecord as ResponseGetPaymentRecord;
use Monext\Payline\PaylineApi\Response\GetPaymentRecordFactory as ResponseGetPaymentRecordFactory;
use Monext\Payline\PaylineApi\Response\GetWebPaymentDetails as ResponseGetWebPaymentDetails;
use Monext\Payline\PaylineApi\Response\GetWebPaymentDetailsFactory as ResponseGetWebPaymentDetailsFactory;
use Monext\Payline\PaylineApi\Response\ManageWebWallet as ResponseManageWebWallet;
use Monext\Payline\PaylineApi\Response\ManageWebWalletFactory as ResponseManageWebWalletFactory;
use Monolog\Logger as LoggerConstants;
use Payline\PaylineSDK;
use Psr\Log\LoggerInterface as Logger;

class Client
{
    /**
     * @var PaylineSDKFactory
     */
    protected $paylineSDKFactory;

    /**
     * @var PaylineSDK
     */
    protected $paylineSDK;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var ResponseDoWebPaymentFactory
     */
    protected $responseDoWebPaymentFactory;

    /**
     * @var ResponseDoCaptureFactory
     */
    protected $responseDoCaptureFactory;

    /**
     * @var ResponseDoVoidFactory
     */
    protected $responseDoVoidFactory;

    /**
     * @var ResponseDoRefundFactory
     */
    protected $responseDoRefundFactory;

    /**
     * @var ResponseGetMerchantSettingsFactory
     */
    protected $responseGetMerchantSettingsFactory;

    /**
     * @var ResponseGetWebPaymentDetailsFactory
     */
    protected $responseGetWebPaymentDetailsFactory;

    /**
     * @var ResponseManageWebWalletFactory
     */
    protected $responseManageWebWalletFactory;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var EncryptorInterface
     */
    protected $encryptor;

    /**
     * @var ModuleListInterface
     */
    protected $moduleList;

    /**
     * @var ResponseGetPaymentRecordFactory
     */
    protected $responseGetPaymentRecordFactory;

    /**
     * @var ProductMetadata
     */
    protected $productMetadata;

    /**
     * @var DirectoryList
     */
    protected $directoryList;


    /**
     * Client constructor.
     * @param \Monext\Payline\PaylineApi\PaylineSDKFactory $paylineSDKFactory
     * @param ScopeConfigInterface $scopeConfig
     * @param ResponseDoWebPaymentFactory $responseDoWebPaymentFactory
     * @param ResponseDoCaptureFactory $responseDoCaptureFactory
     * @param ResponseDoVoidFactory $responseDoVoidFactory
     * @param ResponseDoRefundFactory $responseDoRefundFactory
     * @param ResponseGetMerchantSettingsFactory $responseGetMerchantSettingsFactory
     * @param ResponseGetWebPaymentDetailsFactory $responseGetWebPaymentDetailsFactory
     * @param ResponseManageWebWalletFactory $responseManageWebWalletFactory
     * @param ResponseGetPaymentRecordFactory $responseGetPaymentRecordFactory
     * @param Logger $logger
     * @param EncryptorInterface $encryptor
     * @param ModuleListInterface $moduleList
     * @param ProductMetadata $productMetadata
     * @param DirectoryList $directoryList
     */
    public function __construct(
        PaylineSDKFactory $paylineSDKFactory,
        ScopeConfigInterface $scopeConfig,
        ResponseDoWebPaymentFactory $responseDoWebPaymentFactory,
        ResponseDoCaptureFactory $responseDoCaptureFactory,
        ResponseDoVoidFactory $responseDoVoidFactory,
        ResponseDoRefundFactory $responseDoRefundFactory,
        ResponseGetMerchantSettingsFactory $responseGetMerchantSettingsFactory,
        ResponseGetWebPaymentDetailsFactory $responseGetWebPaymentDetailsFactory,
        ResponseManageWebWalletFactory $responseManageWebWalletFactory,
        ResponseGetPaymentRecordFactory $responseGetPaymentRecordFactory,
        Logger $logger,
        EncryptorInterface $encryptor,
        ModuleListInterface $moduleList,
        ProductMetadata $productMetadata,
        DirectoryList $directoryList
    ) {
        $this->paylineSDKFactory = $paylineSDKFactory;
        $this->scopeConfig = $scopeConfig;
        $this->responseDoWebPaymentFactory = $responseDoWebPaymentFactory;
        $this->responseDoCaptureFactory = $responseDoCaptureFactory;
        $this->responseDoVoidFactory= $responseDoVoidFactory;
        $this->responseDoRefundFactory= $responseDoRefundFactory;
        $this->responseGetMerchantSettingsFactory = $responseGetMerchantSettingsFactory;
        $this->responseGetWebPaymentDetailsFactory = $responseGetWebPaymentDetailsFactory;
        $this->responseManageWebWalletFactory = $responseManageWebWalletFactory;
        $this->logger = $logger;
        $this->encryptor = $encryptor;
        $this->moduleList = $moduleList;
        $this->productMetadata = $productMetadata;
        $this->responseGetPaymentRecordFactory = $responseGetPaymentRecordFactory;
        $this->directoryList = $directoryList;
    }

    /**
     * @param RequestDoWebPayment $request
     * @return ResponseDoWebPayment
     */
    public function callDoWebPayment(RequestDoWebPayment $request)
    {
        $this->initPaylineSDK();

        $response = $this->responseDoWebPaymentFactory->create();

        $data = $request->getData();
        foreach ($data['order']['details'] as $orderDetail) {
            $this->paylineSDK->addOrderDetail($orderDetail);
        }
        unset($data['order']['details']);

        $response->fromData(
            $this->paylineSDK->doWebPayment($data)
        );

        $this->logApiCall($request, $response);

        return $response;
    }

    /**
     * @param RequestDoCapture $request
     * @return ResponseDoCapture
     */
    public function callDoCapture(RequestDoCapture $request)
    {
        $this->initPaylineSDK();

        $response = $this->responseDoCaptureFactory->create();
        $response->fromData(
            $this->paylineSDK->doCapture($request->getData())
        );

        $this->logApiCall($request, $response);

        return $response;
    }

    /**
     * @param RequestDoVoid $request
     * @return ResponseDoVoid
     */
    public function callDoVoid(RequestDoVoid $request)
    {
        $this->initPaylineSDK();

        $response = $this->responseDoVoidFactory->create();
        $response->fromData(
            $this->paylineSDK->doReset($request->getData())
        );

        $this->logApiCall($request, $response);

        return $response;
    }

    /**
     * @param RequestDoRefund $request
     * @return ResponseDoRefund
     */
    public function callDoRefund(RequestDoRefund $request)
    {
        $this->initPaylineSDK();

        $response = $this->responseDoRefundFactory->create();
        $response->fromData(
            $this->paylineSDK->doRefund($request->getData())
        );

        $this->logApiCall($request, $response);

        return $response;
    }

    /**
     * @param RequestGetMerchantSettings $request
     * @return ResponseGetMerchantSettings
     */
    public function callGetMerchantSettings(RequestGetMerchantSettings $request)
    {
        $this->initPaylineSDK();

        $response = $this->responseGetMerchantSettingsFactory->create();
        $response->fromData(
            $this->paylineSDK->getMerchantSettings($request->getData())
        );

        $this->logApiCall($request, $response);

        return $response;
    }

    /**
     * @param RequestGetWebPaymentDetails $request
     * @return ResponseGetWebPaymentDetails
     */
    public function callGetWebPaymentDetails(RequestGetWebPaymentDetails $request)
    {
        $this->initPaylineSDK();

        /** @var ResponseGetWebPaymentDetails $response */
        $response = $this->responseGetWebPaymentDetailsFactory->create();
        $response->fromData(
            $this->paylineSDK->getWebPaymentDetails($request->getData())
        );

        $this->logApiCall($request, $response);

        return $response;
    }

    /**
     * @param RequestGetPaymentRecord $request
     * @return ResponseGetPaymentRecord
     */
    public function callGetPaymentRecord(RequestGetPaymentRecord $request)
    {
        $this->initPaylineSDK();

        /** @var ResponseGetPaymentRecord $response */
        $response = $this->responseGetPaymentRecordFactory->create();
        $response->fromData(
            $this->paylineSDK->getPaymentRecord($request->getData())
        );

        $this->logApiCall($request, $response);

        return $response;
    }

    /**
     * @param RequestManageWebWallet $request
     * @return ResponseManageWebWallet
     */
    public function callManageWebWallet(RequestManageWebWallet $request)
    {
        $this->initPaylineSDK();

        $response = $this->responseManageWebWalletFactory->create();
        $response->fromData(
            $this->paylineSDK->manageWebWallet($request->getData())
        );

        $this->logApiCall($request, $response);

        return $response;
    }

    /**
     * @return $this
     */
    protected function initPaylineSDK()
    {

        $logSdkDir = 'payline_sdk';
        $logSdkPath = $this->directoryList->getPath(DirectoryList::LOG) . '/' . $logSdkDir;
        if (!file_exists($logSdkPath)) {
            if(!mkdir($logSdkPath)) {
                $logSdkPath = $this->directoryList->getPath(DirectoryList::LOG);
            }
        }

        // Do not RESET Singleton if sdk::privateData is not resetable
        if(isset($this->paylineSDK) && method_exists($this->paylineSDK, 'reset')) {
// Need more tests to uncomment
//            $this->paylineSDK->reset();
//            $this->logger->log(LoggerConstants::DEBUG, 'Reset and use local paylineSDK');
//            return $this->paylineSDK;
        }


        $logLevel = $this->scopeConfig->getValue(HelperConstants::CONFIG_PATH_PAYLINE_GENERAL_DEBUG, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if (!$logLevel || (int)$logLevel<LoggerConstants::DEBUG) {
            $logLevel = LoggerConstants::ERROR;
        }

        // TODO Handle Proxy
        $paylineSdkParams = array(
            'merchant_id' => $this->scopeConfig->getValue(HelperConstants::CONFIG_PATH_PAYLINE_GENERAL_MERCHANT_ID,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE),
            'access_key' => $this->encryptor->decrypt($this->scopeConfig->getValue(HelperConstants::CONFIG_PATH_PAYLINE_GENERAL_ACCESS_KEY,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE)),
            'proxy_host' => null,
            'proxy_port' => null,
            'proxy_login' => null,
            'proxy_password' => null,
            'environment' => $this->scopeConfig->getValue(HelperConstants::CONFIG_PATH_PAYLINE_GENERAL_ENVIRONMENT,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE),
            'pathLog' => $logSdkPath . '/',
            'logLevel' => $logLevel
        );


        $this->logger->log(LoggerConstants::DEBUG, 'create paylineSDKFactory', $this->arrayClearSdkDataToLog($paylineSdkParams));

        $this->paylineSDK = $this->paylineSDKFactory->create($paylineSdkParams);
        $currentModule = $this->moduleList->getOne(HelperConstants::MODULE_NAME);
        $this->paylineSDK->usedBy(      HelperConstants::PAYLINE_API_USED_BY_PREFIX . ' ' .
            $this->productMetadata->getVersion() . ' - '
            .' v'.$currentModule['setup_version']);

        if(method_exists($this->paylineSDK, 'setFailoverOptions')) {
            if($this->scopeConfig->getValue(HelperConstants::CONFIG_PATH_PAYLINE_GENERAL_DISABLE_FAILOVER,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
                $this->paylineSDK->setFailoverOptions('disabled', true);
            }
        }


        return $this;
    }


    /**
     * @param AbstractRequest $request
     * @param AbstractResponse $response
     */
    protected function logApiCall(\Monext\Payline\PaylineApi\AbstractRequest $request,
                                  \Monext\Payline\PaylineApi\AbstractResponse $response) {

        $logLevel =  $response->isSuccess() ? LoggerConstants::DEBUG : LoggerConstants::ERROR;
        $this->logger->log($logLevel,
            get_class($request),
            ['Request'=> $this->arrayClearSdkDataToLog($request->getData()),
                'Response'=> $this->arrayClearSdkDataToLog($response->getData())]);

    }

    /**
     * @param array $privateData
     * @return $this
     */
    protected function addPrivateDataToPaylineSDK(array $privateData)
    {
        foreach ($privateData as $privateDataItem) {
            $this->paylineSDK->addPrivateData($privateDataItem);
        }
        return $this;
    }


    /**
     * @param $arrayToClean
     * @param string[] $encryptKeys
     * @return bool|string
     */
    protected function arrayClearSdkDataToLog($arrayToClean, $encryptKeys = ['access_key'])
    {
        foreach ($encryptKeys as $key) {
            if(isset($arrayToClean[$key]) ) {
                $keyLength = strlen($arrayToClean[$key]);
                if($keyLength > 6) {
                    $arrayToClean[$key] = preg_replace('/^(.{4}).{'.($keyLength  - 6).'}(.*)$/', '$1'.str_repeat("x", $keyLength - 6).'$2', $arrayToClean[$key]);
                } else {
                    $arrayToClean[$key] = "xxxxxx".$key."xxxxxx";
                }
            }
        }

        array_walk_recursive($arrayToClean, function(&$item, $key) {
            if(preg_match('/Logo$/', $key, $match)) {$item="xxxxxx".$match[1]."xxxxxx";}

        });


        return $arrayToClean;
    }
}
