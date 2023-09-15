<?php

namespace Monext\Payline\Model\ResourceModel\OrderIncrementIdToken;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init('Monext\Payline\Model\OrderIncrementIdToken', 'Monext\Payline\Model\ResourceModel\OrderIncrementIdToken');
    }


    /**
     * Before load action
     *
     * @return $this
     */
    protected function _beforeLoad()
    {
        $select = $this->getSelect();
        $select->columns(['created_from_second' => new \Zend_Db_Expr('TIMESTAMPDIFF(SECOND, main_table.created_at, now())')]);
        return $this;
    }
}
