<?php

namespace Monext\Payline\Model\PaymentTypeManagement;

use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Sales\Model\Order as Order;
use Magento\Sales\Model\Order\Payment as OrderPayment;
use Monext\Payline\Helper\Constants as HelperConstants;
use Monext\Payline\Helper\Data as PaylineHelper;
use Monext\Payline\Model\OrderManagement;
use Monext\Payline\PaylineApi\Constants as PaylineApiConstants;
use Monext\Payline\PaylineApi\Response\GetWebPaymentDetails as ResponseGetWebPaymentDetails;
use Monext\Payline\PaylineApi\Response\GetPaymentRecord as ResponseGetPaymentRecord;
use Magento\Sales\Model\Order\Payment\Transaction\ManagerInterface;
use Psr\Log\LoggerInterface as Logger;

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
     * @var ManagerInterface
     */
    protected $transactionManager;

    /**
     * @var Logger
     */
    protected $paylineLogger;

    /**
     * AbstractPaymentTypeManagement constructor.
     * @param PaylineHelper $helperData
     * @param OrderManagement $paylineOrderManagement
     * @param ManagerInterface $transactionManager
     * @param Logger $paylineLogger
     */
    public function __construct(
        PaylineHelper $helperData,
        OrderManagement $paylineOrderManagement,
        ManagerInterface $transactionManager,
        Logger $paylineLogger
    )
    {
        $this->helperData = $helperData;
        $this->paylineOrderManagement = $paylineOrderManagement;
        $this->transactionManager = $transactionManager;
        $this->paylineLogger = $paylineLogger;
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

        //Keep all payment data
        $this->helperData->setPaymentAdditionalInformation($payment, $response, ['payment']);

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

    public function handlePaymentRecord(ResponseGetPaymentRecord $response, OrderPaymentInterface $payment)
    {
    }
}
