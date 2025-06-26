<?php

namespace Monext\Payline\Model\System\Config\Source\WidgetCustomization;

use Magento\Framework\Data\OptionSourceInterface;

class BorderRadius implements OptionSourceInterface
{

    public function toOptionArray()
    {
        return [
            [
                'value' => '',
                'label' => __('Payline default'),
            ],
            [
                'value' => 'none',
                'label' => __('Aucun')
            ],
            [
                'value' => 'small',
                'label' => __('Small')
            ],
            [
                'value' => 'average',
                'label' => __('Average')
            ],
            [
                'value' => 'big',
                'label' => __('Big')
            ]
        ];
    }
}
