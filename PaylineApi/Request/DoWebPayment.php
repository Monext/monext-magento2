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

    public function setCart(CartInterface $cart)
    {
        $this->cart = $cart;
        return $this;
    }

    public function setProductCollection(ProductCollection $productCollection)
    {
        $this->productCollection = $productCollection;
        return $this;
    }

    public function setBillingAddress(AddressInterface $billingAddress)
    {
        $this->billingAddress = $billingAddress;
        return $this;
    }

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
        $merchantName = $this->helperData->getMerchantName();

        $customer = $this->cart->getCustomer();
        $cutomerIdKey = $customer->getId() ? $customer->getId() : '0000';
        $data['payment']['softDescriptor'] = $this->cleanAndSubstr($merchantName . date('yymd') . $this->cart->getReservedOrderId() . $cutomerIdKey, 0, 64);
    }



    protected function prepareOrderData(&$data)
    {
        $data['order']['ref'] = $this->cart->getReservedOrderId();
        //TODO: Add origin to manage smartdisplay
        //$data['order']['origin'] = '';
        $data['order']['country'] = $this->billingAddress->getCountry();
        $data['order']['amount'] = $this->helperData->mapMagentoAmountToPaylineAmount($this->totals->getGrandTotal());
        $taxes = $this->helperData->mapMagentoAmountToPaylineAmount($this->totals->getTaxAmount());
        $data['order']['taxes'] = $taxes ? $taxes : null;
        $data['order']['currency'] = $this->helperCurrency->getNumericCurrencyCode($this->totals->getBaseCurrencyCode());
        $data['order']['date'] = $this->formatDateTime($this->cart->getCreatedAt());
        $data['order']['comment'] = 'Magento order';
        $this->prepareOrderDetailsData($data);
        $this->prepareOrderDeliveryData($data);
    }

    protected function prepareOrderDetailsData(&$data)
    {
        $data['order']['details'] = [];

        foreach ($this->cart->getItems() as $item) {
            $tmpProduct = $this->productCollection->getItemById($item->getProductId());
            $orderDetail = [
                'ref' => $item->getSku(),
                'price' => $this->helperData->mapMagentoAmountToPaylineAmount($item->getPrice()),
                'quantity' => $item->getQty(),
                'brand' => $tmpProduct->getManufacturer(),
                'category' => $tmpProduct->getPaylineCategoryMapping(),
                'taxRate' => $this->helperData->mapMagentoAmountToPaylineAmount($item->getTaxPercent()),
                'comment' => 'Magento item'
            ];

            $data['order']['details'][] = $orderDetail;
        }
    }

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

        $data['buyer']['title'] =  $this->getCustomerTitle($this->billingAddress->getPrefix());
        $data['buyer']['mobilePhone'] = $this->helperData->getNormalizedPhoneNumber($this->shippingAddress->getTelephone()) ??
            $this->helperData->getNormalizedPhoneNumber($this->billingAddress->getTelephone()) ??
            '0123456789';

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
        //$cleanString = iconv('UTF-8', "ASCII//TRANSLIT", $string);

        $cleanString = str_replace(array("\r", "\n", "\t"), array('', '', ''), $string);
        if (function_exists('mb_substr')) {
            $cleanString = mb_substr($cleanString, $offset, $length, 'UTF-8');
        } else {
            $cleanString = substr($cleanString, $offset, $length);
        }

        return $cleanString;
    }

}
