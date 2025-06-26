<?php

namespace Monext\Payline\Model\System\Config\Source\WidgetCustomization;

use Magento\Framework\Data\OptionSourceInterface;

class CtaBackgroundColors implements OptionSourceInterface
{

    public function toOptionArray()
    {
        return [
            [
                'value' => '',
                'label' => __('Payline default'),
            ],
            [
                'value' => '#000000',
                'label' => __('Black')
            ],
            [
                'value' => '#d64c1d',
                'label' => __('Red')
            ],
            [
                'value' => '#00786c',
                'label' => __('Green')
            ],
            [
                'value' => '#42414f',
                'label' => __('Dark gray')
            ],
            [
                'value' => '#e6d001',
                'label' => __('Yellow')
            ],
            [
                'value' => 'hexadecimal',
                'label' => __('Hexadecimal')
            ],
        ];
    }
}
