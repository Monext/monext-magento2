<?php

namespace Monext\Payline\Model\System\Config\Source\WidgetCustomization;

use Magento\Framework\Data\OptionSourceInterface;

class ChangeColorValue implements OptionSourceInterface
{

    public function toOptionArray()
    {
        return [
            [
                'value' => '-30',
                'label' => __('%1 darker', '30%')
            ],
            [
                'value' => '-20',
                'label' => __('%1 darker', '20%')
            ],
            [
                'value' => '-10',
                'label' => __('%1 darker', '10%')
            ],
            [
                'value' => '',
                'label' => __('No change'),
            ],
            [
                'value' => '10',
                'label' => __('%1 lighter', '10%')
            ],
            [
                'value' => '20',
                'label' => __('%1 lighter', '20%')
            ],
            [
                'value' => '30',
                'label' => __('%1 lighter', '30%')
            ],
        ];
    }
}

