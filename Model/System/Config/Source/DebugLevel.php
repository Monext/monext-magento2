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

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return [Logger::ERROR => __('Error'),
            Logger::WARNING => __('Warning'),
            Logger::NOTICE => __('Notice'),
            Logger::INFO => __('Info'),
            Logger::DEBUG => __('Debug')
            ];
    }
}
