<?php

namespace Monext\Payline\Plugin\Model\ResourceModel;

use Magento\Sales\Model\Order as OrderModel;
use Magento\Sales\Model\ResourceModel\Order;
use Monext\Payline\Model\PaymentManagement;
use Monext\Payline\Helper\Data as PaylineHelperData;

class OrderPlugin
{
    protected PaylineHelperData $paylineHelper;
    protected PaymentManagement $paymentManagement;

    /**
     * @param PaylineHelperData $paylineHelper
     * @param PaymentManagement $paymentManagement
     */
    public function __construct(PaylineHelperData $paylineHelper,
                                PaymentManagement $paymentManagement)
    {
        $this->paylineHelper = $paylineHelper;
        $this->paymentManagement = $paymentManagement;
    }


    /**
     * @param Order $subject
     * @param $result
     * @param OrderModel $object
     * @return Order
     */
    public function afterSave(Order $subject, $result, OrderModel $object)
    {
        $oldStatus = $object->getOrigData('status');
        $newStatus = $object->getData('status');
        if ($newStatus && $oldStatus && $oldStatus == $newStatus){
            return $result;
        }

        $captureOnTriggerValue = $this->paylineHelper->getCaptureCptOnTriggerOrderStatus();

        if ($captureOnTriggerValue == $newStatus) {
            $this->paymentManagement->captureOnTrigger($object);
        }
        return $result;
    }
}
