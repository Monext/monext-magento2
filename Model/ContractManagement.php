<?php

namespace Monext\Payline\Model;

use Magento\Framework\App\CacheInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Monext\Payline\Helper\Constants as HelperConstants;
use Monext\Payline\Model\ContractFactory;
use Monext\Payline\Model\ResourceModel\Contract\Collection as ContractCollection;
use Monext\Payline\Model\ResourceModel\Contract\CollectionFactory as ContractCollectionFactory;
use Monext\Payline\PaylineApi\Client as PaylineApiClient;
use Monext\Payline\PaylineApi\Constants as PaylineApiConstants;
use Monext\Payline\PaylineApi\Request\GetMerchantSettingsFactory as RequestGetMerchantSettingsFactory;

class ContractManagement
{
    /**
     * @var CacheInterface
     */
    protected $cache;

    /**
     * @var ContractFactory
     */
    protected $contractFactory;

    /**
     * @var PaylineApiClient
     */
    protected $paylineApiClient;

    /**
     * @var RequestGetMerchantSettingsFactory
     */
    protected $requestGetMerchantSettingsFactory;

    /**
     * @var ContractCollectionFactory
     */
    protected $contractCollectionFactory;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var ContractCollection
     */
    protected $usedContracts;

    protected $forbiddenContractsByAction = [];

    public function __construct(
        CacheInterface $cache,
        ContractFactory $contractFactory,
        PaylineApiClient $paylineApiClient,
        RequestGetMerchantSettingsFactory $requestGetMerchantSettingsFactory,
        ContractCollectionFactory $contractCollectionFactory,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->cache = $cache;
        $this->contractFactory = $contractFactory;
        $this->paylineApiClient = $paylineApiClient;
        $this->requestGetMerchantSettingsFactory = $requestGetMerchantSettingsFactory;
        $this->contractCollectionFactory = $contractCollectionFactory;
        $this->scopeConfig = $scopeConfig;
    }

    public function refreshContracts()
    {
        $this->cache->remove(HelperConstants::CACHE_KEY_MERCHANT_CONTRACT_IMPORT_FLAG);
        return $this;
    }

    public function importContracts()
    {
        $contractsFlag = $this->cache->load(HelperConstants::CACHE_KEY_MERCHANT_CONTRACT_IMPORT_FLAG);

        if (!$contractsFlag) {
            $request = $this->requestGetMerchantSettingsFactory->create();
            $response = $this->paylineApiClient->callGetMerchantSettings($request);

            if ($response->isSuccess()) {
                // TODO Create a contract repository class
                $contractCollection = $this->contractCollectionFactory->create();

                foreach ($response->getContractsData() as $contractData) {
                    $contract = $contractCollection->getItemByColumnValue('number', $contractData['number']);
                    if (!$contract || !$contract->getId()) {
                        $contract = $this->contractFactory->create();
                    }

                    $contract->addData($contractData);
                    $contract->setIsUpdated(1);
                    $contract->save();
                }

                foreach ($contractCollection as $contract) {
                    if (!$contract->getIsUpdated()) {
                        $contract->delete();
                    }
                }

                $this->cache->save("1", HelperConstants::CACHE_KEY_MERCHANT_CONTRACT_IMPORT_FLAG);
            }
        }

        return $this;
    }

    public function getUsedContracts()
    {
        if (!isset($this->usedContracts)) {
            $this->usedContracts = $this->contractCollectionFactory->create()
                ->addFieldToFilter('id', ['in' => $this->scopeConfig->getValue(HelperConstants::CONFIG_PATH_PAYLINE_GENERAL_CONTRACTS, \Magento\Store\Model\ScopeInterface::SCOPE_STORE)]);
        }

        return $this->usedContracts;
    }

    /**
     * @param $action
     * @return false|mixed|ContractCollection
     */
    public function getForbiddenContractsForAction($action)
    {
        if(!isset($this->forbiddenContractsByAction[$action])) {
            $forbiddenCardType = [];
            switch ($action) {
                case PaylineApiConstants::PAYMENT_ACTION_AUTHORIZATION:
                    $forbiddenCardType = PaylineApiConstants::CONTRACT_CARD_TYPE_AUTHORIZATION_FORBIDDEN;
                    break;
                case PaylineApiConstants::PAYMENT_ACTION_AUTHORIZATION_CAPTURE:
                    $forbiddenCardType = PaylineApiConstants::CONTRACT_CARD_TYPE_AUTHORIZATION_CAPTURE_FORBIDDEN;
                    break;
            }

            if($forbiddenCardType) {
                $contractCollection = $this->contractCollectionFactory->create();
                $contractCollection->addFieldToFilter('card_type', ['in' => $forbiddenCardType]);
                $this->forbiddenContractsByAction[$action] = $contractCollection;
            } else {
                $this->forbiddenContractsByAction[$action] = false;
            }
        }


        return $this->forbiddenContractsByAction[$action];
    }



    public function getNonRefundContracts() {
        $contractCollection = $this->contractCollectionFactory->create();
        $contractCollection->addFieldToFilter('card_type', ['in' => PaylineApiConstants::CONTRACT_CARD_TYPE_REFUND_FORBIDDEN]);

        return $contractCollection;
    }


    /**
     * @deprecated
     *
     * DO not used but only for test to avoid error
     *
     *     [result] => Array
    (
    [code] => 02716
    [shortMessage] => REFUSED
    [longMessage] => SelectedContractList must be filled with only one contract per payment method or enable the option Isolate the payment method in the payment page
    )

     *
     * @return array
     */
    public function getUsedContractsByDisctinctType()
    {
        $usedContracts = $this->getUsedContracts();
        return array_values(array_column($usedContracts->getData(), 'number', 'card_type'));
    }

}
