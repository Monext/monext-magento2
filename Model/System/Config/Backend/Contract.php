<?php


namespace Monext\Payline\Model\System\Config\Backend;

use \Monext\Payline\Helper\Constants;
use Monext\Payline\Helper\Data as HelperData;

class Contract extends \Magento\Framework\App\Config\Value
{
    /**
     * @var HelperData
     */
    private $helperData;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        HelperData $helperData,
        array $data = []
    ) {
        $this->helperData = $helperData;
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }

    public function beforeSave()
    {
        //var_dump(__METHOD__);

        $contracts = $this->getValue();
        $action = $this->getFieldsetDataValue(Constants::CONFIG_PATH_PAYLINE_CPT_ACTION);
        $action = $this->getFieldsetDataValue('payment/payline/payline_solutions/payline_cpt/payment_action');
        $contracts = [];

        if (!$this->helperData->isActionAvailableForContract($action, $contracts)) {
            $msg = __('Invalid action for selected contracts %s %s', [$action, $contracts]);
            throw new \Magento\Framework\Exception\LocalizedException($msg);
        }
        return parent::beforeSave();
    }
}
