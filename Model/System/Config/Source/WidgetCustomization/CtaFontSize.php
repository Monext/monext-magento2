<?php

namespace Monext\Payline\Model\System\Config\Source\WidgetCustomization;

use Magento\Framework\Data\OptionSourceInterface;

class CtaFontSize implements OptionSourceInterface
{

    public function toOptionArray()
    {
        return [
            [
                'value' => '',
                'label' => __('Payline default'),
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
            ],
        ];
    }
}
