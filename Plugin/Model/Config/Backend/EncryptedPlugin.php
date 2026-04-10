<?php

namespace Monext\Payline\Plugin\Model\Config\Backend;

use Magento\Config\Model\Config\Backend\Encrypted;
use Monext\Payline\Helper\Constants;

class EncryptedPlugin
{
    /** @var \Monext\Payline\Helper\Data  */
    private \Monext\Payline\Helper\Data $helper;

    /**
     * @param \Monext\Payline\Helper\Data $helper
     */
    public function __construct(\Monext\Payline\Helper\Data $helper)
    {
        $this->helper = $helper;
    }

    /**
     * @param Encrypted $subject
     * @return null
     */
    public function beforeBeforeSave(Encrypted $subject)
    {
        $value = $subject->getValue();
        $groupId = $subject->getGroupId();

        if ($groupId == 'payline_common' && $value == $this->helper->maskAccessKey($value) ) {
            $subject->setValue('**************');
        }

        return null;
    }

}
