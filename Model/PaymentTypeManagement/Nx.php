<?php

namespace Monext\Payline\Model\PaymentTypeManagement;

use Magento\Sales\Model\Order\Payment as OrderPayment;
use Monext\Payline\PaylineApi\Response\GetWebPaymentDetails as ResponseGetWebPaymentDetails;
use Monext\Payline\Helper\Constants as HelperConstants;
use Monext\Payline\PaylineApi\Constants as PaylineApiConstants;
use Monext\Payline\PaylineApi\Response\GetPaymentRecord as ResponseGetPaymentRecord;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Sales\Model\Order as Order;

class Nx extends AbstractPaymentTypeManagement
{
    public function validate(
        ResponseGetWebPaymentDetails $response,
        OrderPayment $payment
    )
    {
        //pas de validation possible $response->getAmount() !== $payment->getOrder()->getGrandTotal()
        return true;
    }

    /**
     * @param ResponseGetWebPaymentDetails $response
     * @param OrderPayment $payment
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function handlePaymentSuccess(ResponseGetWebPaymentDetails $response, OrderPayment $payment)
    {
        $payment->setAdditionalInformation('payment_cycling', $response->getBillingRecords(['date', 'amount', 'rank']));
        $payment->setAdditionalInformation('payment_record_id', $response->getPaymentRecordId());
        parent::handlePaymentSuccess($response, $payment);
    }

    public function handlePaymentRecord(ResponseGetPaymentRecord $response, OrderPaymentInterface $payment)
    {
        $nbTxnSuccess = 0;
        foreach ($response->getBillingRecords() as $record) {
            if (in_array($record['status'], PaylineApiConstants::PAYMENT_BACK_CODES_RETURN_CYCLING_SUCCESS)) {
                ++$nbTxnSuccess;
                if ($this->checkIfTransactionExists($record['transaction']['id'], $payment) === false) {
                    $payment->setTransactionId($record['transaction']['id']);
                    $payment->setParentTransactionId($payment->getLastTransId());
                    $payment->setTransactionAdditionalInfo('payline_record', $record['transaction']);
                    $payment->registerCaptureNotification($this->helperData->mapPaylineAmountToMagentoAmount($record['amount']), true);
                    $orderIsUpdated = true;
                }
            } elseif (in_array($record['status'], PaylineApiConstants::PAYMENT_BACK_CODES_RETURN_CYCLING_ERROR)) {
                $payment->getOrder()->addStatusHistoryComment(__('Error code %1 => %2', $record['result']['code'], $record['result']['longMessage']), false);
                $orderIsUpdated = true;
            }
        }

        $this->paylineLogger->debug('Count billing records : ' . count($response->getBillingRecords()));
        $this->paylineLogger->debug('Nb records Sucess : ' . $nbTxnSuccess);
        if (count($response->getBillingRecords()) === $nbTxnSuccess) {
            $switchStatus = false;
            if (
                $payment->getOrder()->getState() === Order::STATE_COMPLETE
                && $payment->getOrder()->getStatus() === HelperConstants::ORDER_STATUS_PAYLINE_CYCLE_PAYMENT_CAPTURE
            ) {
                $switchStatus = true;
            }
            $this->paylineLogger->debug('Switch Status : ' . $switchStatus);
            $payment->getOrder()->addStatusHistoryComment(__('All payment cycle received'), $switchStatus);
            $payment->getOrder()->setPaiementCompleted(true);
            $orderIsUpdated = true;
        } else {
            $payment->getOrder()->setPaiementCompleted(false);
        }

        $isPaymentCyclingCompleted = ($payment->getOrder()->getPaiementCompleted()) ? '1' : '0';
        $this->paylineLogger->debug('Paiement Cycling Completed : ' . $isPaymentCyclingCompleted);
        $this->paylineLogger->debug('Order status : ' . $payment->getOrder()->getStatus());
        $this->paylineLogger->debug('Order state : ' . $payment->getOrder()->getState());

        //save_mode => flag pour la livraison après ou pendant les échéances
        if ($orderIsUpdated === true && !$payment->getOrder()->hasData('save_mode')) {
            $payment->getOrder()->save();
        }
    }

    /**
     * @param $transactionId
     * @param OrderPayment $payment
     * @return bool
     */
    protected function checkIfTransactionExists($transactionId, OrderPayment $payment)
    {
        return $this->transactionManager->isTransactionExists(
            $transactionId,
            $payment->getId(),
            $payment->getOrder()->getId()
        );
    }
}
