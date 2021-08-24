<?php

namespace Monext\Payline\Model\System\Config\Backend;

use \Monext\Payline\Helper\Constants;

class Contract extends AbstractValue
{
    function getActionValue()
    {
        return $this->getDataByPath(Constants::CONFIG_PATH_RAW_PAYLINE_CPT_ACTION);
    }

    function getContractsValue()
    {
        return $this->getValue();
    }

    public function beforeSave()
    {
        //TODO: Check wallet activation for EQUENS

        //TODO: Check same card_type with widget integration

        return parent::beforeSave();
    }
}
