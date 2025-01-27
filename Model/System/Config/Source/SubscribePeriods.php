<?php

namespace Monext\Payline\Model\System\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class SubscribePeriods implements OptionSourceInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        $recurringPeriods = [['value' => '0', 'label' => __('No limit')]];

        foreach (range(2, 99) as $period) {
            $recurringPeriods[] = [
                'value' => $period,
                'label' => $period,
            ];
        }

        return $recurringPeriods;
    }
}
