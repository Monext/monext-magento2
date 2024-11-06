<?php

namespace Monext\Payline\Model\System\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Monext\Payline\Helper\Constants;

class AllowedProductType implements OptionSourceInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => Constants::CONFIG_PAYLINE_REC_ALLOWED_PRODUCT_ID,
                'label' => __('Product ID'),
            ],
            [
                'value' => Constants::CONFIG_PAYLINE_REC_ALLOWED_PRODUCT_SKU,
                'label' => __('Product SKU'),
            ],
            [
                'value' => Constants::CONFIG_PAYLINE_REC_ALLOWED_PRODUCT_TYPE,
                'label' => __('Attribute Set'),
            ],
        ];
    }
}
