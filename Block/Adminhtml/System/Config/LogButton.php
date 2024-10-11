<?php

namespace Monext\Payline\Block\Adminhtml\System\Config;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

class LogButton extends Field
{
    protected function _prepareLayout()
    {

        $indexUrl = $this->getUrl('payline/logs/index');
        $this->addChild(
            'open_button',
            \Magento\Backend\Block\Widget\Button::class,
            [
                'id' => 'open_logs_button',
                'label' => __('Open Logs'),
                'onclick' => "window.location = '".$indexUrl."'; return false;"
            ]
        );

        $this->addChild(
            'load_button',
            \Magento\Backend\Block\Widget\Button::class,
            [
                'id' => 'load_logs_button',
                'label' => __('Load Logs'),
                'onclick' => 'javascript:loadLogs(); return false;'
            ]
        );

        $downloadUrl = $this->getUrl('payline/logs/download');

        $this->addChild(
            'download_button',
            \Magento\Backend\Block\Widget\Button::class,
            [
                'id' => 'download_logs_button',
                'label' => __('Download Logs'),
                'onclick' => "window.location = '".$downloadUrl."'; return false;"
            ]
        );

        return parent::_prepareLayout();
    }

    protected function _getElementHtml(AbstractElement $element)
    {
        $openButtonHtml = $this->getChildHtml('open_button');
        $loadButtonHtml = $this->getChildHtml('load_button');
        $downloadButtonHtml = $this->getChildHtml('download_button');
        $element->setData('after_element_html', $openButtonHtml . $loadButtonHtml . $this->getJsScript().$downloadButtonHtml);
        return $element->getElementHtml();
    }

    protected function getJsScript()
    {
        $url = $this->getUrl('payline/logs/load'); // Appel à votre contrôleur
        return <<<SCRIPT
<script type="text/javascript">
    function loadLogs() {
        new Ajax.Request('$url', {
            method: 'GET',
            onSuccess: function(transport) {
                var response = transport.responseText;
                $('log_display').update(response);
            }
        });
    }
</script>
<div class="log_container"><div id="log_display" class="log_display"></div></div>
SCRIPT;
    }
}
