<?php

namespace Monext\Payline\Model\PaymentTypeManagement;

use Magento\Sales\Model\Order as Order;
use Magento\Sales\Model\Order\Payment as OrderPayment;
use Monext\Payline\Helper\Constants as HelperConstants;
use Monext\Payline\Helper\Data as PaylineHelper;
use Monext\Payline\Model\OrderManagement;
use Monext\Payline\PaylineApi\Constants as PaylineApiConstants;
use Monext\Payline\PaylineApi\Response\GetWebPaymentDetails as ResponseGetWebPaymentDetails;

abstract class AbstractPaymentTypeManagement
{
    /**
     * @var PaylineHelper
     */
    protected $helperData;

    /**
     * @var OrderManagement
     */
    protected $paylineOrderManagement;

    /**
     * AbstractPaymentTypeManagement constructor.
     * @param PaylineHelper $helperData
     * @param OrderManagement $paylineOrderManagement
     */
    public function __construct(
        PaylineHelper $helperData,
        OrderManagement $paylineOrderManagement
    )
    {
        $this->helperData = $helperData;
        $this->paylineOrderManagement = $paylineOrderManagement;
    }

    abstract public function validate(ResponseGetWebPaymentDetails $response, OrderPayment $payment);

    public function handlePaymentCanceled(OrderPayment $payment, $message = null)
    {
        $this->paylineOrderManagement->handleSetOrderStateStatus(
            $payment->getOrder(),
            Order::STATE_CANCELED,
            HelperConstants::ORDER_STATUS_PAYLINE_CANCELED,
            $message ?? $payment->getData('payline_error_message')
        );
    }



    protected function handlePaymentData(
        ResponseGetWebPaymentDetails $response,
        OrderPayment $payment
    ) {
        $transactionData = $response->getTransactionData();
        $payment->setTransactionId($transactionData['id']);

        //In widget mode or with NX payment there is no contract_number
        if(!$payment->getAdditionalInformation('contract_number') && $response->getContractNumber()) {
            $payment->setAdditionalInformation('contract_number', $response->getContractNumber());
        }

    }

    public function handlePaymentSuccess(
        ResponseGetWebPaymentDetails $response,
        OrderPayment $payment
    ) {
        $this->handlePaymentData($response, $payment);

        $paymentData = $response->getPaymentData();

        // TODO Add controls to avoid double authorization/capture
        if ($paymentData['action'] == PaylineApiConstants::PAYMENT_ACTION_AUTHORIZATION) {
            $payment->setIsTransactionClosed(false);
            $payment->authorize(false, $this->helperData->mapPaylineAmountToMagentoAmount($paymentData['amount']));
        } elseif ($paymentData['action'] == PaylineApiConstants::PAYMENT_ACTION_AUTHORIZATION_CAPTURE) {
            $payment->getMethodInstance()->setSkipCapture(true);
            $payment->capture();
        }
    }

    // TODO: Need to asssociate a transaction
    public function handlePaymentWaitingAcceptance(ResponseGetWebPaymentDetails $response,
                                                   OrderPayment $payment)
    {
        //$this->handlePaymentData($response, $payment);
    }
}
