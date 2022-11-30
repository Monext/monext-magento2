<?php

namespace Monext\Payline\Model\System\Config\Source;

use Magento\Framework\Option\ArrayInterface;
use Monext\Payline\Helper\Constants;

class TokenUsage implements ArrayInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => Constants::TOKEN_USAGE_ONCE, 'label' => __('Renew token (default)')],
            ['value' => Constants::TOKEN_USAGE_ONCE_HISTORY, 'label' => __('Renew token and keep history')],
            ['value' => Constants::TOKEN_USAGE_RECYCLE, 'label' => __('Recycle existing token')],
        ];
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return [Constants::TOKEN_USAGE_ONCE =>  __('Renew token'),
            Constants::TOKEN_USAGE_ONCE_HISTORY =>  __('Renew token and keep history (beta)'),
            Constants::TOKEN_USAGE_RECYCLE =>  __('Recycle existing token (beta)'),
            ];
    }
}
