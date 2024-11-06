<?php

namespace Monext\Payline\Model\System\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class StartDate implements OptionSourceInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => 0,
                'label' => __('Due day'),
            ],
            [
                'value' => 1,
                'label' => __('After a period'),
            ],
            [
                'value' => 2,
                'label' => __('After two periods'),
            ],
        ];
    }
}
