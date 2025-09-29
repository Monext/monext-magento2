<?php

namespace Monext\Payline\Model\System\Config\Source\WidgetCustomization;

use Magento\Framework\Data\OptionSourceInterface;

class CtaTextColor implements OptionSourceInterface
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
                'label' => __('Dark')
            ],
            [
                'value' => '#FFFFFF',
                'label' => __('Light')
            ],
        ];
    }
}
