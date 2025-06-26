<?php

namespace Monext\Payline\Model\System\Config\Source\WidgetCustomization;

use Magento\Framework\Data\OptionSourceInterface;

class ChangeColorValue implements OptionSourceInterface
{

    public function toOptionArray()
    {
        return [
            [
                'value' => '',
                'label' => __('No'),
            ],
            [
                'value' => '10',
                'label' => '10%'
            ],
            [
                'value' => '20',
                'label' => '20%'
            ],
            [
                'value' => '30',
                'label' => '30%'
            ],
        ];
    }
}

