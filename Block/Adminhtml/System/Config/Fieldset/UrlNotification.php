<?php

namespace Monext\Payline\Block\Adminhtml\System\Config\Fieldset;

use Magento\Config\Block\System\Config\Form\Fieldset;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\View\Helper\SecureHtmlRenderer;
use Magento\Framework\Url;

class UrlNotification extends Fieldset
{

    protected Url $urlBuilder;

    public function __construct(\Magento\Backend\Block\Context      $context,
                                \Magento\Backend\Model\Auth\Session $authSession,
                                \Magento\Framework\View\Helper\Js   $jsHelper,
                                Url                                 $urlBuilder,
                                array                               $data = [],
                                ?SecureHtmlRenderer                 $secureRenderer = null)
    {
        parent::__construct($context, $authSession, $jsHelper, $data, $secureRenderer);
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * Return header comment part of html for fieldset
     *
     * @param AbstractElement $element
     * @return string
     */
    protected function _getHeaderCommentHtml($element)
    {
        $notificationUrl = $this->urlBuilder->getUrl('payline/webpayment/notifyfrompaymentgateway');

        $html = '<div class="message message-notice notice">';
        $html .= '<div>';
        $html .= sprintf(__('You must define the notification URL into your point of sale configuration. %s The notification URL is: %s %s %s'), '<br />', '<strong>', $notificationUrl, '</strong>');
        $html .= '<div></div>';
        $html .= __('When editing the URL, please be sure to check all the checkbox below the text input too.');
        $html .= '</div></div>';

        return $html;
    }
}
