<?php

namespace Monext\Payline\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Monext\Payline\Api\Data\OrderIncrementIdTokenInterface;

class OrderIncrementIdToken extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('payline_order_increment_id_token', 'id');
    }


    protected function _getLoadSelect($field, $value, $object)
    {
        $select = parent::_getLoadSelect($field, $value, $object);
        $select->columns(['created_from_second' => new \Zend_Db_Expr('TIMESTAMPDIFF(SECOND, created_at, now())')]);

        return $select;
    }
}
