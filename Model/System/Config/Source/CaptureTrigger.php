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
            'label' => __('When shipment is created')
        );

        $options[]  = array(
            'value' => HelperConstants::PAYLINE_CPT_CAPTURE_ON_INVOICE,
            'label' => __('When invoice is created')
        );

        $stateStatuses = array_diff($this->_stateStatuses, [Order::STATE_CANCELED]);
        $statuses = $this->_orderConfig->getStateStatuses($stateStatuses);

        foreach($statuses as $code => $label) {
            $options[]  = array(
                'value' => $code,
                'label' => __('When order status is "%1"', __($label))
            );
        }

        return $options;
    }
}
