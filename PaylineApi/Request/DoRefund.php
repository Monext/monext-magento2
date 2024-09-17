<?php

namespace Monext\Payline\PaylineApi\Request;

use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Monext\Payline\Model\ContractManagement;
use Monext\Payline\PaylineApi\AbstractRequest;
use Monext\Payline\PaylineApi\Constants as PaylineApiConstants;

class DoRefund extends AbstractRequest
{
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var array
     */
    protected $paymentData;

    /**
     * @var ContractManagement
     */
    protected $contractManagement;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        ContractManagement $contractManagement
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->contractManagement = $contractManagement;
    }

    public function setPaymentData(array $paymentData)
    {
        $this->paymentData = $paymentData;
        return $this;
    }


    public function getData()
    {
        $data = array();

        // PAYMENT
        $data['payment'] = $this->paymentData;
        $data['payment']['action'] = PaylineApiConstants::PAYMENT_ACTION_REFUND;

        // Transaction ID
        $data['transactionID'] = $data['payment']['transactionID'];
        unset($data['payment']['transactionID']);
        // Same for comment
        $data['comment'] = $data['payment']['comment'];
        unset($data['payment']['comment']);

        // PRIVATE DATA LIST
        $data['privateDataList'] = array();

        // SEQUENCE NUMBER
        $data['sequenceNumber'] = '';

        // MEDIA
        $data['media'] = '';

        return $data;
    }
}
