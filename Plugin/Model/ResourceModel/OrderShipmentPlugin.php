<?php

namespace Monext\Payline\Plugin\Model\ResourceModel;

use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\ResourceModel\Order\Shipment;
use Magento\Sales\Model\Order\Shipment as ShipmentModel;
use Monext\Payline\Model\PaymentManagement;
use Monext\Payline\Helper\Data as PaylineHelperData;
use Magento\Sales\Model\OrderRepository;

class OrderShipmentPlugin
{

    protected PaylineHelperData $paylineHelper;
    protected OrderRepository $orderRepository;
    protected PaymentManagement $paymentManagement;

    /**
     * @param PaylineHelperData $paylineHelper
     * @param OrderRepository $orderRepository
     * @param PaymentManagement $paymentManagement
     */
    public function __construct(PaylineHelperData $paylineHelper,
                                OrderRepository   $orderRepository,
                                PaymentManagement $paymentManagement)
    {
        $this->paylineHelper = $paylineHelper;
        $this->orderRepository = $orderRepository;
        $this->paymentManagement = $paymentManagement;
    }

    /**
     * @param Shipment $subject
     * @param $result
     * @param ShipmentModel $shipment
     * @return void
     * @throws InputException
     * @throws NoSuchEntityException
     */
    public function afterSave(Shipment $subject, $result, ShipmentModel $shipment)
    {
        if ($this->paylineHelper->getCaptureCptOnTriggerShipment()) {
            /** @var Order $order */
            $order = $this->orderRepository->get($shipment->getOrderId());

            $this->paymentManagement->captureOnTrigger($order);
        }
    }
}
