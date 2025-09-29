<?php

namespace Monext\Payline\Model\Method\WebPayment;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Asset\Repository as AssetRepository;
use Magento\Payment\Helper\Data as PaymentHelper;
use Magento\Payment\Model\MethodInterface;
use Magento\Store\Model\ScopeInterface;
use Monext\Payline\Helper\Constants as HelperConstants;
use Monext\Payline\Model\ContractManagement;
use Monext\Payline\Model\Method\AbstractMethodConfigProvider;

class CptConfigProvider extends AbstractMethodConfigProvider
{
    /**
     * @var ContractManagement
     */
    protected $contractManagement;

    /**
     * @var MethodInterface
     */
    protected $method;

    private $cspNonceProvider;

    private $objectManager;

    public function __construct(
        PaymentHelper $paymentHelper,
        AssetRepository $assetRepository,
        ContractManagement $contractManagement,
        UrlInterface $urlBuilder,
        ScopeConfigInterface $scopeConfig,
        ObjectManagerInterface $objectManager,
    ) {
        parent::__construct($paymentHelper, $assetRepository, $contractManagement, $urlBuilder, $scopeConfig);
        $this->method = $this->paymentHelper->getMethodInstance(HelperConstants::WEB_PAYMENT_CPT);
        $this->objectManager = $objectManager;
        if(class_exists(\Magento\Csp\Helper\CspNonceProvider::class)) {
            $this->cspNonceProvider = $this->objectManager->get(\Magento\Csp\Helper\CspNonceProvider::class);
        }
    }

    /**
     * @return array
     * @throws \ReflectionException
     */
    public function getConfig()
    {
        $config = [];
        $config['payment']['paylineWebPaymentCpt']['integrationType'] = $this->getMethodConfigData('integration_type');
        $config['payment']['paylineWebPaymentCpt']['widgetDisplay'] = $this->getMethodConfigData('widget_display');
        $config['payment']['paylineWebPaymentCpt']['dataEmbeddedredirectionallowed'] = !empty($this->getMethodConfigData('iframe_3ds')) ? 'true' : 'false';

        $config['payment']['paylineWebPaymentCpt']['nonce'] = false;
        if($this->cspNonceProvider) {
            $config['payment']['paylineWebPaymentCpt']['nonce'] = $this->cspNonceProvider->generateNonce();
        }

        if($this->scopeConfig->getValue(HelperConstants::CONFIG_PATH_PAYLINE_WIDGET_CUSTOMIZATION_ENABLED, ScopeInterface::SCOPE_STORE)) {
            $widgetCustomization = [];
            $widgetCustomization['widget_cta_label'] = $this->getMethodConfigData('widget_cta_label');
            $widgetCustomization['widget_cta_text_under'] = $this->getMethodConfigData('widget_cta_text_under');
            $config['payment']['paylineWebPaymentCpt']['widgetCustomization'] = $widgetCustomization;
        }
        return $config;
    }
}
