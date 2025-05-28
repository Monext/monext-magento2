<?php

namespace Monext\Payline\PaylineApi\Request;

use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\UrlInterface;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Api\Data\PaymentInterface;
use Monext\Payline\Helper\Constants as HelperConstants;
use Monext\Payline\Helper\Currency as HelperCurrency;
use Monext\Payline\Helper\Data as HelperData;
use Monext\Payline\Model\ContractManagement;
use Monext\Payline\PaylineApi\AbstractRequest;

class DoWebPayment extends AbstractRequest
{
    /**
     * @var CartInterface
     */
    protected $cart;

    /**
     * @var ProductCollection
     */
    protected $productCollection;

    /**
     * @var DataObject
     */
    protected $totals;

    /**
     * @var PaymentInterface
     */
    protected $payment;

    /**
     * @var AddressInterface
     */
    protected $billingAddress;

    /**
     * @var AddressInterface
     */
    protected $shippingAddress;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var HelperCurrency
     */
    protected $helperCurrency;

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var ContractManagement
     */
    protected $contractManagement;

    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * @var DateTime
     */
    protected $dateTime;

    /**
     * @var DateTime\Timezone
     */
    protected $timezone;

    /**
     * @var DoWebPaymentTypeFactory
     */
    protected $doWebPaymentTypeFactory;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        HelperCurrency $helperCurrency,
        HelperData $helperData,
        UrlInterface $urlBuilder,
        ContractManagement $contractManagement,
        DateTime $dateTime,
        DateTime\Timezone $timezone,
        DoWebPaymentTypeFactory $doWebPaymentTypeFactory
    )
    {
        $this->scopeConfig = $scopeConfig;
        $this->helperCurrency = $helperCurrency;
        $this->helperData = $helperData;
        $this->urlBuilder = $urlBuilder;
        $this->contractManagement = $contractManagement;
        $this->dateTime = $dateTime;
        $this->timezone = $timezone;
        $this->doWebPaymentTypeFactory = $doWebPaymentTypeFactory;
    }

    /**
     * @param CartInterface $cart
     * @return $this
     */
    public function setCart(CartInterface $cart)
    {
        $this->cart = $cart;
        return $this;
    }

    /**
     * @param ProductCollection $productCollection
     * @return $this
     */
    public function setProductCollection(ProductCollection $productCollection)
    {
        $this->productCollection = $productCollection;
        return $this;
    }

    /**
     * @param AddressInterface $billingAddress
     * @return $this
     */
    public function setBillingAddress(AddressInterface $billingAddress)
    {
        $this->billingAddress = $billingAddress;
        return $this;
    }

    /**
     * @param AddressInterface|null $shippingAddress
     * @return $this
     */
    public function setShippingAddress(AddressInterface $shippingAddress = null)
    {
        $this->shippingAddress = $shippingAddress;
        return $this;
    }

    /**
     * @param DataObject $totals
     * @return $this
     */
    public function setTotals(DataObject $totals)
    {
        $this->totals = $totals;
        return $this;
    }

    /**
     * @param PaymentInterface $payment
     * @return $this
     */
    public function setPayment(PaymentInterface $payment)
    {
        $this->payment = $payment;
        return $this;
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getData()
    {
        if (!isset($this->data)) {
            $data = parent::getData();

            $this->preparePaymentData($data);
            $this->prepareOrderData($data);
            $this->prepareBuyerData($data);
            $this->prepareBillingAddressData($data);
            $this->prepareShippingAddressData($data);
            $this->preparePrivateData($data);

            $data['languageCode'] = $this->scopeConfig->getValue(HelperConstants::CONFIG_PATH_PAYLINE_GENERAL_LANGUAGE,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

            $paymentMethod = $this->payment->getMethod();
            $data['customPaymentPageCode'] = $this->scopeConfig->getValue('payment/'.$paymentMethod.'/custom_payment_page_code',
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

            $this->doWebPaymentTypeFactory->create($this->payment)->getData($data);
            $this->data = $data;
        }

        return $this->data;
    }

    /**
     * @param $data
     * @return void
     */
    protected function preparePaymentData(&$data)
    {
        $paymentMethod = $this->payment->getMethod();
        $paymentAdditionalInformation = $this->payment->getAdditionalInformation();

        $data['payment']['amount'] = $this->helperData->mapMagentoAmountToPaylineAmount($this->totals->getGrandTotal());
        $data['payment']['currency'] = $this->helperCurrency->getNumericCurrencyCode($this->totals->getBaseCurrencyCode());
        $data['payment']['action'] = $this->scopeConfig->getValue('payment/' . $paymentMethod . '/payment_action',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $data['payment']['mode'] = $paymentAdditionalInformation['payment_mode'];

        $this->addSoftDescriptor($data);
    }

    /**
     * Fill with the recommendation below:
     * -MerchantName
     * -Transaction date (string 6: YYMMDD)
     * -Order/ reference
     * -Buyer / customerId
     */
    protected function addSoftDescriptor(&$data)
    {

        $softDescriptorFormat = $this->scopeConfig->getValue(HelperConstants::CONFIG_PATH_PAYLINE_GENERAL_SOFT_DESCRIPTOR, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $merchantName = $this->helperData->getMerchantName();

        if($softDescriptorFormat && $merchantName) {
            $customer = $this->cart->getCustomer();
            $cutomerIdKey = $customer->getId() ? $customer->getId() : '0000';

            /** @see https://developer.paypal.com/docs/api/orders/v2/#orders_create : soft_descriptor */
            $data['payment']['softDescriptor'] = $this->cleanAndSubstr($merchantName . $this->cart->getReservedOrderId() . date('yymd'), 0, 22);
        }

    }


    /**
     * @param $data
     * @return void
     * @throws \Exception
     */
    protected function prepareOrderData(&$data)
    {
        $data['order']['ref'] = $this->cart->getReservedOrderId();
        //TODO: Add origin to manage smartdisplay
        //$data['order']['origin'] = '';
        $data['order']['country'] = $this->billingAddress->getCountry();
        $data['order']['amount'] = $this->helperData->mapMagentoAmountToPaylineAmount($this->totals->getGrandTotal());
        $taxes = $this->helperData->mapMagentoAmountToPaylineAmount($this->totals->getTaxAmount());
        $data['order']['taxes'] = $taxes ? $taxes : 0;
        $data['order']['currency'] = $this->helperCurrency->getNumericCurrencyCode($this->totals->getBaseCurrencyCode());
        $data['order']['date'] = $this->formatDateTime($this->cart->getCreatedAt());
        $data['order']['comment'] = 'Magento order';
        $this->prepareOrderDetailsData($data);
        $this->prepareOrderDeliveryData($data);
    }

    /**
     * @param $data
     * @return void
     */
    protected function prepareOrderDetailsData(&$data)
    {
        $data['order']['details'] = [];
        $lastTaxRate = 0;
        $lastCategory = 0;

        foreach ($this->cart->getItems() as $item) {
            $tmpProduct = $this->productCollection->getItemById($item->getProductId());
            $orderDetail = [
                'ref' => $item->getSku(),
                'price' => $this->helperData->mapMagentoAmountToPaylineAmount($item->getPriceInclTax()),
                'quantity' => $item->getQty(),
                'brand' => $tmpProduct->getManufacturer(),
                'category' => $tmpProduct->getPaylineCategoryMapping(),
                'taxRate' => $this->helperData->mapMagentoAmountToPaylineAmount($item->getTaxPercent()),
                'comment' => 'Magento item'
            ];

            $lastTaxRate = $this->helperData->mapMagentoAmountToPaylineAmount($item->getTaxPercent());
            $lastCategory = $tmpProduct->getPaylineCategoryMapping();

            $data['order']['details'][] = $orderDetail;
        }


        if($this->totals->getDiscountAmount()) {
            $orderDetail = [
                'ref' => 'CART_DISCOUNT',
                'price' => -1 * round(abs($this->totals->getDiscountAmount()) * 100),
                'quantity' => 1,
                'comment' => 'Cart amount adjustment',
                'category' =>  $lastCategory,
                'taxRate' => $lastTaxRate,
            ];
            $data['order']['details'][] = $orderDetail;
        }

    }

    /**
     * @param $data
     * @return void
     * @throws \Exception
     */
    protected function prepareOrderDeliveryData(&$data)
    {

        if (!$this->cart->getIsVirtual()) {
            $deliveryData = [
                'deliveryTime' => $this->helperData->getDefaultDeliveryTime(),
                'deliveryMode' => $this->helperData->getDefaultDeliveryMode(),
                'deliveryExpectedDelay' => $this->helperData->getDefaultDeliveryExpectedDelay(),
            ];
            $objectShippingMethod = $this->shippingAddress->getShippingMethod();
            $addressConfig        = $this->helperData->getDeliverySetting();
            if ($objectShippingMethod && !empty($addressConfig)) {
                foreach ($addressConfig as $shippingMethodConfig) {
                    if ($shippingMethodConfig['shipping_method'] == $objectShippingMethod) {
                        $deliveryData['deliveryTime'] = $shippingMethodConfig['deliverytime'];
                        $deliveryData['deliveryMode'] = $shippingMethodConfig['deliverymode'];
                        $deliveryData['deliveryExpectedDelay'] = $shippingMethodConfig['delivery_expected_delay'];
                        $deliveryData = array_filter($deliveryData);
                        break;
                    }
                }
            }

            $deliveryData['deliveryCharge'] = $this->helperData->mapMagentoAmountToPaylineAmount($this->shippingAddress->getShippingInclTax());

            if($deliveryData['deliveryExpectedDelay']) {
                $deliveryData['deliveryExpectedDate'] = $this->getDeliveryExpectedDate($deliveryData['deliveryExpectedDelay']);
            }

            $data['order'] = array_merge($data['order'], $deliveryData);
        }
    }

    /**
     * @param $expectedDelay
     *
     *
     * @return false|string Order.ExpectedDeliveryDate : Required (format : dd/MM/yyyy or dd/MM/yyyy HH:mm:ss)
     * @throws \Exception
     */
    protected function getDeliveryExpectedDate($expectedDelay)
    {
        $expectedDelay = (int)$expectedDelay;
        $currentDate = new \DateTime();
        $expectedDate = $currentDate->add(new \DateInterval('P'.$expectedDelay.'D'));

        return $expectedDate->format('d/m/Y');
    }

    /**
     * @param $data
     * @return void
     */
    protected function prepareBuyerData(&$data)
    {
        $customer = $this->cart->getCustomer();
        $paymentMethod = $this->payment->getMethod();

        foreach (['lastName' => 'getLastname', 'firstName' => 'getFirstname', 'email' => 'getEmail'] as $dataIdx => $getter) {
            $tmpData = $customer->$getter();

            if (empty($tmpData)) {
                $tmpData = $this->billingAddress->$getter();
            }
            $data['buyer'][$dataIdx] = $tmpData;

            if ($dataIdx == 'email') {
                if (!$this->helperData->isEmailValid($tmpData)) {
                    unset($data['buyer']['email']);
                }

                $data['buyer']['customerId'] = $tmpData;
            }
        }

        $mobilePhone = '0123456789';
        if ($this->shippingAddress
            && $this->shippingAddress->getTelephone()
            && $this->helperData->getNormalizedPhoneNumber($this->shippingAddress->getTelephone())
        ) {
            $mobilePhone = $this->shippingAddress->getTelephone();
        } elseif ($this->billingAddress
            && $this->billingAddress->getTelephone()
            && $this->helperData->getNormalizedPhoneNumber($this->billingAddress->getTelephone())
        ) {
            $mobilePhone = $this->billingAddress->getTelephone();
        }

        $data['buyer']['title'] =  $this->getCustomerTitle($this->billingAddress->getPrefix());
        $data['buyer']['mobilePhone'] = $mobilePhone;

        if ($customer->getId()) {
            $data['buyer']['accountCreateDate'] = $this->formatDateTime($customer->getCreatedAt(), 'd/m/y');
        }

        if ($this->helperData->isWalletEnabled($paymentMethod)) {
            if ($customer->getId() && $customer->getCustomAttribute('wallet_id')->getValue()) {
                $data['buyer']['walletId'] = $customer->getCustomAttribute('wallet_id')->getValue();
            } else {
                $data['buyer']['walletId'] = $this->helperData->generateRandomWalletId();
            }
        }
    }

    /**
     * @param $data
     * @return void
     */
    protected function prepareBillingAddressData(&$data)
    {
        $data['billingAddress']['title'] = $this->getCustomerTitle($this->billingAddress->getPrefix());
        $data['billingAddress']['firstName'] = $this->cleanAndSubstr($this->billingAddress->getFirstname(), 0, 100);
        $data['billingAddress']['lastName'] = $this->cleanAndSubstr($this->billingAddress->getLastname(), 0, 100);
        $data['billingAddress']['cityName'] = $this->cleanAndSubstr($this->billingAddress->getCity(), 0, 40);
        $data['billingAddress']['zipCode'] = substr($this->billingAddress->getPostcode(), 0, 12);
        $data['billingAddress']['country'] = $this->billingAddress->getCountry();
        $data['billingAddress']['state'] = $this->billingAddress->getRegion();

        $billingPhone = $this->helperData->getNormalizedPhoneNumber($this->billingAddress->getTelephone());
        if ($billingPhone) {
            $data['billingAddress']['phone'] = $billingPhone;
            $data['billingAddress']['phoneType'] = 1;
        }

        $streetData = $this->billingAddress->getStreet();
        for ($i = 0; $i <= 1; $i++) {
            if (isset($streetData[$i])) {
                $data['billingAddress']['street' . ($i + 1)] = $this->cleanAndSubstr($streetData[$i], 0, 100);
            }
        }

        $name = $this->helperData->buildPersonNameFromParts(
            $this->billingAddress->getFirstname(),
            $this->billingAddress->getLastname(),
            $this->billingAddress->getPrefix()
        );
        $data['billingAddress']['name'] = $this->cleanAndSubstr($name, 0, 100);
    }

    /**
     * @param $data
     * @return void
     */
    protected function prepareShippingAddressData(&$data)
    {
        if (!$this->cart->getIsVirtual() && isset($this->shippingAddress)) {

            $data['shippingAddress']['title'] = $this->getCustomerTitle($this->shippingAddress->getPrefix());
            $data['shippingAddress']['firstName'] = $this->cleanAndSubstr($this->shippingAddress->getFirstname(), 0, 100);
            $data['shippingAddress']['lastName'] = $this->cleanAndSubstr($this->shippingAddress->getLastname(), 0, 100);
            $data['shippingAddress']['cityName'] = $this->cleanAndSubstr($this->shippingAddress->getCity(), 0, 40);
            $data['shippingAddress']['zipCode'] = substr($this->shippingAddress->getPostcode(), 0, 12);
            $data['shippingAddress']['country'] = $this->shippingAddress->getCountry();
            $data['shippingAddress']['state'] = $this->shippingAddress->getRegion();

            $shippingPhone = $this->helperData->getNormalizedPhoneNumber($this->shippingAddress->getTelephone());
            if ($shippingPhone) {
                $data['shippingAddress']['phone'] = $shippingPhone;
                $data['shippingAddress']['phoneType'] = 1;
            }

            $streetData = $this->shippingAddress->getStreet();
            for ($i = 0; $i <= 1; $i++) {
                if (isset($streetData[$i])) {
                    $data['shippingAddress']['street' . ($i + 1)] = $this->cleanAndSubstr($streetData[$i], 0, 100);
                }
            }

            $name = $this->helperData->buildPersonNameFromParts(
                $this->shippingAddress->getFirstname(),
                $this->shippingAddress->getLastname(),
                $this->shippingAddress->getPrefix()
            );
            $data['shippingAddress']['name'] = $this->cleanAndSubstr($name, 0, 100);
        }
    }

    /**
     * @param $data
     * @return void
     */
    protected function preparePrivateData(&$data)
    {
        $privateData[] = array('key' => 'OrderSaleChannel', 'value' => 'DESKTOP');

        $smartDisplayParameter = $this->scopeConfig->getValue(HelperConstants::CONFIG_PATH_PAYLINE_GENERAL_SMARTDISPLAY_PARAM,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if (!empty($smartDisplayParameter)) {
            $privateData[] = array('key' => 'display.rule.param', 'value' => $smartDisplayParameter);
        }

        $data['privateData'] = $privateData;
    }

    /**
     * @param $prefix
     * @return mixed
     */
    protected function getCustomerTitle($prefix)
    {
        $title = $this->helperData->getDefaultPrefix();
        if ($this->billingAddress->getPrefix() && $prefixConfig = $this->helperData->getPrefixSetting()) {
            foreach ($prefixConfig as $prefixMapping) {
                if ($prefixMapping['customer_prefix'] == $prefix) {
                    $title = $prefixMapping['customer_title'];
                    break;
                }
            }
        }

        return $title;
    }

    /**
     * @param $string
     * @param $offset
     * @param null $length
     * @return false|string
     */
    protected function cleanAndSubstr($string , $offset , $length = null)
    {
        $cleanString = str_replace(array("\r", "\n", "\t"), array('', '', ''), $string);
        if (function_exists('mb_substr')) {
            $cleanString = mb_substr($cleanString, $offset, $length, 'UTF-8');
        } else {
            $cleanString = substr($cleanString, $offset, $length);
        }

        return $cleanString;
    }

}
