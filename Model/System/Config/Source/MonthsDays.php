<?php

namespace Monext\Payline\Model\System\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class MonthsDays implements OptionSourceInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        foreach (range(0, 28) as $dayNum) {
            $recurringPeriods[] = [
                'value' => $dayNum,
                'label' => $dayNum,
            ];
        }

        return $recurringPeriods;
    }
}
