<?php


namespace Monext\Payline\Model\System\Config\Backend;


use Monext\Payline\PaylineApi\Constants as PaylineApiConstants;

abstract class AbstractValue extends \Magento\Framework\App\Config\Value
{

    /**
     * @var \Monext\Payline\Model\ContractManagement
     */
    protected $contractManagement;


    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     * @param \Monext\Payline\Model\ContractManagement $contractManagement
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(\Magento\Framework\Model\Context                        $context,
                                \Magento\Framework\Registry                             $registry,
                                \Magento\Framework\App\Config\ScopeConfigInterface      $config,
                                \Magento\Framework\App\Cache\TypeListInterface          $cacheTypeList,
                                \Monext\Payline\Model\ContractManagement                $contractManagement,
                                \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
                                \Magento\Framework\Data\Collection\AbstractDb           $resourceCollection = null,
                                array                                                   $data = []
    )
    {
        $this->contractManagement = $contractManagement;
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }


    abstract function getActionValue();

    abstract function getContractsValue();

    public function beforeSave()
    {
        $action = $this->getActionValue();
        $contractIds = $this->getContractsValue();

        if($forbiddenContracts = $this->contractManagement->getForbiddenContractsForAction($action)) {
            switch ($action) {
                case PaylineApiConstants::PAYMENT_ACTION_AUTHORIZATION:
                    $actionLabel = __('Authorize');
                    break;
                case PaylineApiConstants::PAYMENT_ACTION_AUTHORIZATION_CAPTURE:
                    $actionLabel = __('Authorize and Capture');
                    break;
            }

            foreach ($forbiddenContracts as $contract) {
                if(in_array($contract->getId(), $contractIds)) {
                    $errorMsg = __('Cannot save configuration, payment action "%1" is not compatible with contract "%2"', [$actionLabel, $contract->getLabel()]);
                    throw new \Magento\Framework\Exception\LocalizedException($errorMsg);
                }
            }
        }

        return parent::beforeSave();
    }
}
