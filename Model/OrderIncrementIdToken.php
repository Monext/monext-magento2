<?php

namespace Monext\Payline\Model;

use Magento\Framework\Model\AbstractModel;

class OrderIncrementIdToken extends AbstractModel implements \Monext\Payline\Api\Data\OrderIncrementIdTokenInterface
{
    protected function _construct()
    {
        $this->_init('Monext\Payline\Model\ResourceModel\OrderIncrementIdToken');
    }

    /**
     * Test token availability (no more 12 min)
     *
     * @param $expireIn
     * @return bool
     */
    public function expireSoon($expireIn=12)
    {
        return !$this->getData('created_from_second') or (floor($this->getData('created_from_second')) > ( $expireIn * 60 ));
    }
}
