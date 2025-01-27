<?php

namespace Monext\Payline\Model\Method\WebPayment;

use Magento\Quote\Api\Data\CartInterface;
use Magento\Sales\Model\Order\Payment as OrderPayment;
use Monext\Payline\Model\Method\AbstractMethod;
use Monext\Payline\Helper\Constants as HelperConstants;

class Rec extends AbstractMethod
{
    protected $_code = HelperConstants::WEB_PAYMENT_REC;

    protected $_isInitializeNeeded = true;

    protected $_isGateway = true;

    protected $_canCapture = true;

    protected $_canRefund = false;

    protected $_canVoid = true;

    protected $_canCapturePartial = true;

    protected $_canRefundInvoicePartial = true;

    public function isAvailable(CartInterface $quote = null)
    {
        return parent::isAvailable($quote) && $this->areQuoteItemsAllowed($quote);
    }

    public function initialize($paymentAction, $stateObject)
    {
        $payment = $this->getInfoInstance();
        $status = HelperConstants::ORDER_STATUS_PAYLINE_PENDING;
        if ($payment instanceof OrderPayment) {
            $order = $payment->getOrder();
            $status = $this->helperData->getMatchingConfigurableStatus($order, $status);

            $quoteId = $order->getQuoteId();
            $result = $this->paylinePaymentManagement->wrapCallPaylineApiDoWebPaymentFacade($quoteId);

            $additionalInformation = $payment->getAdditionalInformation();
            $additionalInformation['do_web_payment_response_data'] = $result;
            $payment->setAdditionalInformation($additionalInformation);
        }

        $stateObject->setData('status', $status);

        return $this;
    }

    /**
     * Return Order place redirect url
     *
     * @return string
     */
    public function getOrderPlaceRedirectUrl()
    {
        return $this->urlBuilder->getUrl('payline/index/rec');
    }

    protected function areQuoteItemsAllowed(CartInterface $quote = null)
    {
        $allowedType = $this->helperData->getRecAllowedType();
        $allowedProductByType = $this->helperData->getRecAllowedProductByType($allowedType);
        if(empty($allowedType) || empty($allowedProductByType)) {
            return false;
        }
        $allowedValues = preg_split('/,|;/',$allowedProductByType);

        $attributeGetter = [
            HelperConstants::CONFIG_PAYLINE_REC_ALLOWED_PRODUCT_SKU => 'getSku',
            HelperConstants::CONFIG_PAYLINE_REC_ALLOWED_PRODUCT_ID => 'getId',
            HelperConstants::CONFIG_PAYLINE_REC_ALLOWED_PRODUCT_TYPE => 'getAttributeSetId',
        ];

        foreach ($quote->getAllVisibleItems() as $quoteItem) {
            $getter = $attributeGetter[$allowedType];
            if (!in_array($quoteItem->getProduct()->$getter(), $allowedValues)) {
                return false;
            }
        }

        return true;
    }
}
