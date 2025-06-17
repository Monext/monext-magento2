<?php

namespace Monext\Payline\Model\System\Config\Source;

use Magento\Sales\Model\Config\Source\Order\Status as OrderStatus;
use Monext\Payline\Helper\Constants as HelperConstants;
use Magento\Sales\Model\Order;

class CaptureTrigger extends OrderStatus
{
    protected $_stateStatuses = [
        \Magento\Sales\Model\Order::STATE_NEW,
        \Magento\Sales\Model\Order::STATE_PROCESSING,
        \Magento\Sales\Model\Order::STATE_COMPLETE
    ];

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $options[]  = array(
            'value' => HelperConstants::PAYLINE_CPT_CAPTURE_ON_SHIPMENT,
            'label' => __('When Shipment is created')
        );

        $options[]  = array(
            'value' => HelperConstants::PAYLINE_CPT_CAPTURE_ON_INVOICE,
            'label' => __('When invoice is created')
        );

        foreach(parent::toOptionArray() as $code => $label) {
            if($code == 0){
                continue;
            }

            $options[]  = array(
                'value' => $label['value'],
                'label' => __('When order status is "%1"', $label['label'])
            );
        }

        return $options;
    }
}
