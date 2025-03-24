<?php

namespace Monext\Payline\Plugin\Model\Order;

use Monext\Payline\Helper\Constants as PaylineConstants;
use Monext\Payline\Helper\Data as PaylineHelperData;
use Magento\Sales\Model\Order\Invoice;

class InvoicePlugin
{
    protected PaylineHelperData $paylineHelper;

    public function __construct(PaylineHelperData $paylineHelper)
    {
        $this->paylineHelper = $paylineHelper;
    }

    public function beforeRegister(Invoice $subject)
    {
        $payment = $subject->getOrder()->getPayment();
        if ($this->paylineHelper->isPaymentFromPayline($payment)
            && $this->paylineHelper->getCaptureCptOnTriggerInvoice()) {
            $subject->setRequestedCaptureCase(Invoice::CAPTURE_ONLINE);
        }
    }
}
