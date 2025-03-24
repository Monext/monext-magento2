<?php

namespace Monext\Payline\Plugin\Model\ResourceModel;

use Magento\Sales\Model\ResourceModel\Order\Shipment;
use Magento\Sales\Model\Order\Shipment as ShipmentModel;
use Monext\Payline\Model\PaymentManagement;
use Monext\Payline\Helper\Data as PaylineHelperData;
use Monext\Payline\Helper\Constants as PaylineConstants;

class OrderShipmentPlugin
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
     * @param Shipment $subject
     * @param $result
     * @param ShipmentModel $shipment
     * @return void
     */
    public function afterSave(Shipment $subject, $result, ShipmentModel $shipment)
    {
        $order = $shipment->getOrder();
        $payment = $order->getPayment();
        if ($this->paylineHelper->isPaymentFromPayline($payment)
            && $this->paylineHelper->getCaptureCptOnTriggerShipment()) {
            $this->paymentManagement->captureOnTrigger($order);
        }
    }
}
