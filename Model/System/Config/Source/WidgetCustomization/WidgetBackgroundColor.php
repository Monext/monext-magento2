<?php

namespace Monext\Payline\Model\System\Config\Source\WidgetCustomization;

use Magento\Framework\Data\OptionSourceInterface;

class WidgetBackgroundColor implements OptionSourceInterface
{

    /**
     * @inheritDoc
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => '',
                'label' => __('Payline default'),
            ],
            [
                'value' => 'lighter',
                'label' => __('Lighter')
            ],
            [
                'value' => 'darker',
                'label' => __('Darker')
            ],
        ];
    }
}
