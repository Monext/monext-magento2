<?php

namespace Monext\Payline\Block\Checkout;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\View\Element\Template;
use Magento\Store\Model\ScopeInterface;
use Monext\Payline\Helper\Constants as PaylineConstants;

class WidgetCustomCss extends Template
{

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    public function __construct(Template\Context     $context,
                                ScopeConfigInterface $scopeConfig,
                                array                $data = [])
    {
        parent::__construct($context, $data);
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @return string
     */
    public function getCustomCss(): string
    {
        $retVal = [
            '#PaylineWidget .pl-text-under-cta { text-align: center; margin-top: 26px; }',
            '#PaylineWidget.pl-container-default .pl-pay-btn-container,
            #PaylineWidget.pl-container-default .pl-pay-btn-container .pl-pay-btn,
            #PaylineWidget.pl-container-default .pl-pay-btn-container .pl-text-under-cta {
                word-break: break-all;
                max-width: 100%;
            }',
            'body #PaylineWidget .pl-wallet-layout .pl-wallets .pl-pay-btn-container {
                max-height: unset;
            }'
        ];

        //--> Cta Background color
        $ctaBgColor = $this->scopeConfig->getValue(
            PaylineConstants::CONFIG_PATH_PAYLINE_WIDGET_CUSTOMIZATION_CTA_BG_COLOR,
            ScopeInterface::SCOPE_STORE
        );

        if ($ctaBgColor) {
            // Si mode hexadecimal, on écrase $ctaBgColor par la valeur hexadécimale si elle existe
            if ($ctaBgColor === 'hexadecimal') {
                $hexValue = $this->scopeConfig->getValue(
                    PaylineConstants::CONFIG_PATH_PAYLINE_WIDGET_CUSTOMIZATION_CTA_BG_COLOR_HEXADECIMAL,
                    ScopeInterface::SCOPE_STORE
                );
                if ($hexValue) {
                    $ctaBgColor = $hexValue;
                } else {
                    $ctaBgColor = '';
                }
            }

            if ($ctaBgColor) {
                $retVal[] = '#PaylineWidget .pl-pay-btn { background-color: ' . $ctaBgColor . '; }';
            }
        }

        //--> Cta hover darker
        $ctaBgColorHover = '';

        $ctaHoverDarker = $this->scopeConfig->getValue(PaylineConstants::CONFIG_PATH_PAYLINE_WIDGET_CUSTOMIZATION_CTA_COLOR_HOVER_DARKER, ScopeInterface::SCOPE_STORE);
        $ctaHoverLighter = $this->scopeConfig->getValue(PaylineConstants::CONFIG_PATH_PAYLINE_WIDGET_CUSTOMIZATION_CTA_COLOR_HOVER_LIGHTER, ScopeInterface::SCOPE_STORE);

        if ($ctaBgColor && $ctaHoverDarker) {
            $ctaBgColorHover = $this->changeColor($ctaBgColor, $ctaHoverDarker, false);
        }

        if ($ctaBgColor && $ctaHoverLighter) {
            $ctaBgColorHover = $this->changeColor($ctaBgColor, $ctaHoverLighter, true);
        }

        if ($ctaBgColorHover) {
            $retVal[] = '#PaylineWidget .pl-pay-btn:hover { background-color: ' . $ctaBgColorHover . '; }';
        }

        //--> Cta Text color
        $ctaColor = $this->scopeConfig->getValue(PaylineConstants::CONFIG_PATH_PAYLINE_WIDGET_CUSTOMIZATION_CTA_COLOR, ScopeInterface::SCOPE_STORE);
        if ($ctaColor) {
            $retVal[] = '#PaylineWidget .pl-pay-btn { color: ' . $ctaColor . '; }';
        }

        //--> FontSize
        $ctaFontSize = $this->scopeConfig->getValue(PaylineConstants::CONFIG_PATH_PAYLINE_WIDGET_CUSTOMIZATION_CTA_FONT_SIZE, ScopeInterface::SCOPE_STORE);
        switch ($ctaFontSize) {
            case 'small':
                $retVal[] = ' #PaylineWidget .pl-pay-btn { font-size: 14px; }';
                break;

            case 'average':
                $retVal[] = ' #PaylineWidget .pl-pay-btn { font-size: 20px; }';
                break;

            case 'big':
                $retVal[] = ' #PaylineWidget .pl-pay-btn { font-size: 24px; }';
                break;
        }

        //--> BorderRadius
        $ctaBorderRadius = $this->scopeConfig->getValue(PaylineConstants::CONFIG_PATH_PAYLINE_WIDGET_CUSTOMIZATION_CTA_BORDER_RADIUS, ScopeInterface::SCOPE_STORE);
        switch ($ctaBorderRadius) {
            case 'none':
                $retVal[] = '#PaylineWidget .pl-pay-btn { border-radius: 0; }';
                break;

            case 'small':
                $retVal[] = '#PaylineWidget .pl-pay-btn { border-radius: 3px; }';
                break;

            case 'average':
                $retVal[] = '#PaylineWidget .pl-pay-btn { border-radius: 8px; }';
                break;

            case 'big':
                $retVal[] = '#PaylineWidget .pl-pay-btn { border-radius: 24px; }';
                break;
        }

        //--> Widget Background
        $widgetBgColor = $this->scopeConfig->getValue(PaylineConstants::CONFIG_PATH_PAYLINE_WIDGET_CUSTOMIZATION_WIDGET_BG_COLOR, ScopeInterface::SCOPE_STORE);
        $cssWidgetBgColor = '';
        switch ($widgetBgColor) {
            case 'lighter':
                $cssWidgetBgColor = '#fefefe';
                break;

            case 'darker':
                $cssWidgetBgColor = '#dfdfdf';
                break;
        }


        if (!empty($cssWidgetBgColor)) {
            $retVal[] = '#PaylineWidget.PaylineWidget.pl-layout-tab .pl-paymentMethods { background-color: ' . $cssWidgetBgColor . '; }';
            $retVal[] = '#PaylineWidget.PaylineWidget.pl-container-default .pl-pmContainer { background-color: ' . $cssWidgetBgColor . '; }';
            $retVal[] = '#PaylineWidget.PaylineWidget.pl-layout-tab .pl-tab.pl-active { background-color: ' . $cssWidgetBgColor . '; }';
        }


        return implode("\n", $retVal);
    }


    /**
     * @return string
     */
    protected function _toHtml()
    {
        $html = '';
        if($this->scopeConfig->getValue(PaylineConstants::CONFIG_PATH_PAYLINE_WIDGET_CUSTOMIZATION_ENABLED, ScopeInterface::SCOPE_STORE)) {
            $css = $this->getCustomCss();
            $html = '<style type="text/css" id="payline-widget-custom-css">' . $css . '</style>';
        }

        return $html;
    }


    protected function changeColor($hex, $strenght, $lighter)
    {
        $hex = ltrim($hex, '#');

        if (strlen($hex) == 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }

        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));

        $factor = $lighter ? 1 + ($strenght / 100) : 1 - ($strenght / 100);

        $r = max(0, min(255, intval($r * $factor)));
        $g = max(0, min(255, intval($g * $factor)));
        $b = max(0, min(255, intval($b * $factor)));

        $newHex = sprintf("#%02x%02x%02x", $r, $g, $b);

        return $newHex;
    }

}
