<?php

namespace Monext\Payline\Model\System\Config\Source;

use Monext\Payline\Helper\Constants as PaylineApiConstants;
use Magento\Framework\Data\OptionSourceInterface;

class CanceledReturn implements OptionSourceInterface
{

    public function toOptionArray()
    {
        return [
            [
                'value' => PaylineApiConstants::PAYLINE_RETURN_CART_EMPTY,
                'label' => __('Empty cart'),
            ],
            [
                'value' => PaylineApiConstants::PAYLINE_RETURN_CART_FULL,
                'label' => __('Cart with items'),
            ],
            [
                'value' => PaylineApiConstants::PAYLINE_RETURN_HISTORY_ORDERS,
                'label' => __('Orders history'),
            ]
        ];
    }
}
