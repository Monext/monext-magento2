<?php

namespace Monext\Payline\Model\System\Config\Source;

use Magento\Framework\Option\ArrayInterface;
use Monolog\Logger;

class DebugLevel implements ArrayInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => Logger::ERROR, 'label' => __('Error')],
            ['value' => Logger::WARNING, 'label' => __('Warning')],
            ['value' => Logger::NOTICE, 'label' => __('Notice')],
            ['value' => Logger::INFO, 'label' => __('Info')],
            ['value' => Logger::DEBUG, 'label' => __('Debug')]
        ];
    }
}
