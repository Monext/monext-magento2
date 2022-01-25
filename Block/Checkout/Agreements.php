<?php
namespace Monext\Payline\Block\Checkout;



class Agreements extends \Magento\CheckoutAgreements\Block\Agreements
{

    protected function _toHtml()
    {
        $agreements = $this->getAgreements();
        if(count($agreements)==0) {
            return '';
        }

        return parent::_toHtml();
    }
}
