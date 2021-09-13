<?php
/*
 *
 *
 * File: Agreements.php
 * Modified on: 29/01/2021 16:41
 *
 * @author Vincent Pietri <vincent.pietri@agence-tbd.com>
 * @copyright 2014 - 2021 TBD, SAS. All rights reserved.
 */

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
