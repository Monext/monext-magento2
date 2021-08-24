<?php


namespace Monext\Payline\Model\System\Config\Backend;

use \Monext\Payline\Helper\Constants;
use Monext\Payline\Helper\Data as HelperData;

class Action extends AbstractValue
{
    function getActionValue()
    {
        return $this->getValue();
    }

    function getContractsValue()
    {
        return $this->getDataByPath(Constants::CONFIG_PATH_RAW_PAYLINE_GENERAL_CONTRACTS);
    }
}
