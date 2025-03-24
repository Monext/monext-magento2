<?php

namespace Monext\Payline\Plugin\Block\Adminhtml\Order\Invoice\Create;

use Magento\Sales\Block\Adminhtml\Order\Invoice\Create\Items;
use Monext\Payline\Helper\Data as PaylineHelperData;
use Monext\Payline\Helper\Constants as PaylineConstants;

class ItemsPlugins
{
    protected PaylineHelperData $paylineHelper;

    public function __construct(PaylineHelperData $paylineHelper)
    {
        $this->paylineHelper = $paylineHelper;
    }

    public function afterIsCaptureAllowed(Items $subject, $result)
    {
        $payment = $subject->getInvoice()->getOrder()->getPayment();
        if ($this->paylineHelper->isPaymentFromPayline($payment)
            && $this->paylineHelper->getCaptureCptOnTriggerInvoice()) {
            return false;
        }
        return $result;
    }
}
