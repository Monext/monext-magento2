<?php

namespace Monext\Payline\Helper;

use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Math\Random as MathRandom;
use Magento\Framework\Serialize\Serializer\Json as Serialize;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Quote\Api\Data\TotalsInterface;
use Monext\Payline\Helper\Constants as HelperConstants;
use Monext\Payline\PaylineApi\Constants as PaylineApiConstants;
use Monext\Payline\PaylineApi\Response\GetWebPaymentDetails as ResponseGetWebPaymentDetails;

class Data extends AbstractHelper
{
    private $delivery = null;

    private $prefix = null;
    /**
     * @var MathRandom
     */
    protected $mathRandom;

    /**
     * @var Serialize
     */
    protected $serialize;


    /**
     * @param Context $context
     * @param MathRandom $mathRandom
     * @param Serialize $serialize
     */
    public function __construct(
        Context $context,
        MathRandom $mathRandom,
        Serialize $serialize
    ) {
        parent::__construct($context);
        $this->mathRandom = $mathRandom;
        $this->serialize = $serialize;
    }

    public function getNormalizedPhoneNumber($phoneNumberCandidate)
    {
        $normalizedPhone = false;
        if(!empty($phoneNumberCandidate)) {
            // "field": "purchase.delivery.recipient.phone_number"
            // format attendu: (+33|508|590|594|596|262|681|687|689)|0033|+33|33|+33(0)|0XXXXXXXXX
            $forbidenPhoneCars = [' ', '.', '(', ')', '-', '/', '\\', '#'];
            //$regexpPhone = '/^\+?[0-9]{1,14}$/';
            $regexpPhone = '/^\+?[0-9]{1,14}$/';

            $normalizedPhone = str_replace($forbidenPhoneCars, '', $phoneNumberCandidate);
            if (!preg_match($regexpPhone, $phoneNumberCandidate)) {
                $normalizedPhone = false;
            }
        }

        return $normalizedPhone;
    }

    public function isEmailValid($emailCandidate)
    {
        $pattern = '/\+/i';

        $charPlusExist = preg_match($pattern, $emailCandidate);
        if (strlen($emailCandidate) <= 50 && \Zend_Validate::is($emailCandidate, 'EmailAddress') && !$charPlusExist) {
            return true;
        } else {
            return false;
        }
    }

    public function buildPersonNameFromParts($firstName, $lastName, $prefix = null)
    {
        $name = '';

        if ($prefix) {
            $name .= $prefix . ' ';
        }
        $name .= $firstName;
        $name .= ' ' . $lastName;

        return $name;
    }

    public function generateRandomWalletId()
    {
        return $this->mathRandom->getRandomString(50);
    }

    public function isWalletEnabled($paymentMethod)
    {
        return $this->scopeConfig->getValue('payment/'.$paymentMethod.'/wallet_enabled',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function mapMagentoAmountToPaylineAmount($magentoAmount)
    {
        return round($magentoAmount * 100, 0);
    }

    public function mapPaylineAmountToMagentoAmount($paylineAmount)
    {
        return $paylineAmount / 100;
    }

    public function getMatchingConfigurableStatus(\Magento\Sales\Model\Order $order, $status)
    {
        if (empty($status)) {
            return null;
        }

        $path = 'payment/' . $order->getPayment()->getMethod() . '/order_status_' . $status;
        if ($configurableStatus = $this->scopeConfig->getValue($path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
            $status = $configurableStatus;
        }
        return $status;
    }

    public function isPaymentQuoteFromPayline(\Magento\Quote\Model\Quote\Payment $payment)
    {
        return in_array($payment->getMethod(),HelperConstants::AVAILABLE_WEB_PAYMENT_PAYLINE);
    }

    public function isPaymentFromPayline(\Magento\Sales\Model\Order\Payment $payment)
    {
        return in_array($payment->getMethod(),HelperConstants::AVAILABLE_WEB_PAYMENT_PAYLINE);
    }

    public function getDeliverySetting() {
        if(is_null($this->delivery)) {
            $this->delivery = [];
            $addressConfigSerialized = $this->scopeConfig->getValue(HelperConstants::CONFIG_PATH_PAYLINE_DELIVERY,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            if ($addressConfigSerialized) {
                try {
                    $this->delivery = $this->serialize->unserialize($addressConfigSerialized);
                } catch (\Exception $e) {
                    $this->_logger->error($e->getMessage());
                }
            }
        }
        return $this->delivery;
    }

    public function getPrefixSetting() {
        if(is_null($this->prefix)) {
            $this->prefix = [];
            $prefixConfigSerialized = $this->scopeConfig->getValue(HelperConstants::CONFIG_PATH_PAYLINE_PREFIX,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            if ($prefixConfigSerialized) {
                try {
                    $this->prefix = $this->serialize->unserialize($prefixConfigSerialized);
                } catch (\Exception $e) {
                    $this->_logger->error($e->getMessage());
                }
            }
        }
        return $this->prefix;
    }

    public function getDefaultDeliveryTime() {
        return $this->scopeConfig->getValue(HelperConstants::CONFIG_PATH_PAYLINE_DEFAULT_DELIVERYTIME);
    }

    public function getDefaultDeliveryMode() {
        return $this->scopeConfig->getValue(HelperConstants::CONFIG_PATH_PAYLINE_DEFAULT_DELIVERYMODE);
    }

    public function getDefaultDeliveryExpectedDelay() {
        return $this->scopeConfig->getValue(HelperConstants::CONFIG_PATH_PAYLINE_DEFAULT_DELIVERY_EXPECTED_DELAY);
    }

    public function getDefaultPrefix() {
        return $this->scopeConfig->getValue(HelperConstants::CONFIG_PATH_PAYLINE_DEFAULT_PREFIX);
    }

    public function getNxMinimumAmountCart($store = null)
    {
        $amount = $this->scopeConfig->getValue(HelperConstants::CONFIG_PATH_PAYLINE_NX_MINIMUM_AMOUNT, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $store);
        $amount = ($amount < 0) ? 0 : $amount;
        return $amount;
    }

    public function getTokenUsage() {
        return $this->scopeConfig->getValue(HelperConstants::CONFIG_PATH_PAYLINE_GENERAL_TOKEN_USAGE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return string
     */
    public function getMerchantName()
    {
        $merchantName = $this->scopeConfig->getValue(HelperConstants::CONFIG_PATH_PAYLINE_GENERAL_MERCHANT_NAME,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE) ??
            $this->scopeConfig->getValue(\Magento\Store\Model\Information::XML_PATH_STORE_INFO_NAME,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE) ??
            '';

        return  preg_replace('/[^A-Z0-9]/', '', strtoupper($merchantName)) ?? 'UNDEFINEDMERCHANTNAME';
    }

    /**
     * @param ResponseGetWebPaymentDetails $response
     * @return mixed
     */
    public function getUserMessageForCode(ResponseGetWebPaymentDetails $response)
    {
        $resultCode = $response->getResultCode();

        $configPath = HelperConstants::CONFIG_PATH_PAYLINE_ERROR_TYPE . substr($resultCode, 1,1);
        $errorMessage = $this->scopeConfig->getValue($configPath, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if(empty($errorMessage)) {
            $errorMessage = $this->scopeConfig->getValue(HelperConstants::CONFIG_PATH_PAYLINE_ERROR_DEFAULT, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        }

        return !empty($errorMessage) ? $errorMessage : $response->getLongErrorMessage();
    }


    public function getDefaultCategories() {
        return array(
            array( 'value' => '1', 'name' => __('Computer (hardware and software)')),
            array( 'value' => '2', 'name' => __('Electronics - TV - Hifi')),
            array( 'value' => '3', 'name' => __('Phone')),
            array( 'value' => '4', 'name' => __('Home appliance')),
            array( 'value' => '5', 'name' => __('Habitat and garden')),
            array( 'value' => '6', 'name' => __('Fashion Clothing')),
            array( 'value' => '7', 'name' => __('Beauty product')),
            array( 'value' => '8', 'name' => __('Jewelry')),
            array( 'value' => '9', 'name' => __('Sport')),
            array( 'value' => '10', 'name' => __('Hobbies')),
            array( 'value' => '11', 'name' => __('Automobiles / motorcycles')),
            array( 'value' => '12', 'name' => __('furnishing')),
            array( 'value' => '13', 'name' => __('children')),
            array( 'value' => '14', 'name' => __('Video games')),
            array( 'value' => '15', 'name' => __('Toys')),
            array( 'value' => '16', 'name' => __('Animals')),
            array( 'value' => '17', 'name' => __('Food')),
            array( 'value' => '18', 'name' => __('Gifts')),
            array( 'value' => '19', 'name' => __('Shows')),
            array( 'value' => '20', 'name' => __('traveling')),
            array( 'value' => '21', 'name' => __('Auction')),
            array( 'value' => '22', 'name' => __('Particular services')),
            array( 'value' => '23', 'name' => __('Professional Services')),
            array( 'value' => '24', 'name' => __('Music')),
            array( 'value' => '25', 'name' => __('Book')),
            array( 'value' => '26', 'name' => __('Photo'))
        );
    }


    /**
     * @return bool
     */
    public function shouldReuseToken()
    {
        if ($this->scopeConfig->getValue('payment/'.HelperConstants::WEB_PAYMENT_CPT.'/integration_type',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE) == PaylineApiConstants::INTEGRATION_TYPE_REDIRECT) {
            return false;
        }

        if($this->getTokenUsage() == HelperConstants::TOKEN_USAGE_RECYCLE) {
            return true;
        }
        return false;
    }


    /**
     * @param CartInterface $cart
     * @param ProductCollection $productCollection
     * @param TotalsInterface $totals
     * @param PaymentInterface $payment
     * @param AddressInterface $billingAddress
     * @param AddressInterface|null $shippingAddress
     * @return string
     */
    public function getCartSha(
        CartInterface $cart,
        ProductCollection $productCollection,
        TotalsInterface $totals,
        PaymentInterface $payment,
        AddressInterface $billingAddress,
        AddressInterface $shippingAddress = null
    ) {

        if(!$cart->getReservedOrderId()) {
            return '';
        }

        $cartDataKeys = [
            $cart->getId(),
            $billingAddress->getCountryId(),
            $shippingAddress->getCountryId(),
            $totals->getGrandTotal(),
            $totals->getTaxAmount(),
            $totals->getBaseCurrencyCode()
        ];

        return sha1(implode(':', $cartDataKeys));
    }

}

