<?php

namespace Monext\Payline\PaylineApi\Request;

use Monext\Payline\PaylineApi\AbstractRequest;
use Monext\Payline\PaylineApi\Constants as PaylineApiConstants;

class DoCapture extends AbstractRequest
{

    /**
     * @var array
     */
    protected $paymentData;


    public function setPaymentData(array $paymentData)
    {
        $this->paymentData = $paymentData;
        return $this;
    }

    public function getData()
    {
        $data = parent::getData();

        // PAYMENT
        $data['payment'] = $this->paymentData;
        $data['payment']['action'] = PaylineApiConstants::PAYMENT_ACTION_CAPTURE;

        // TRANSACTION ID
        $data['transactionID'] = $data['payment']['transactionID'];

        // SEQUENCE NUMBER
        $data['sequenceNumber'] = '';

        return $data;
    }
}
