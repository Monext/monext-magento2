<?php

namespace Monext\Payline\Model\System\Config\Source;

use Magento\Sales\Model\Config\Source\Order\Status as OrderStatus;
use Monext\Payline\Helper\Constants as HelperConstants;

class CaptureTrigger extends OrderStatus
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        $options[]  = array(
            'value' => HelperConstants::PAYLINE_CPT_CAPTURE_ON_SHIPMENT,
            'label' => 'When Shipment is created'
        );

        foreach(parent::toOptionArray() as $code => $label) {
            if($code == 0){
                continue;
            }

            $options[]  = array(
                'value' => $label['value'],
                'label' => __("When order status is '%1'", $label['label'])
            );
        }

        return $options;
    }
}
