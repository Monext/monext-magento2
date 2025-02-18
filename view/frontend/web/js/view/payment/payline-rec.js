/*browser:true*/
/*global define*/
define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (
        Component,
        rendererList
    ) {
        'use strict';

        console.log('payline-rec.js');
        rendererList.push(
            {
                type: 'payline_web_payment_rec',
                component: 'Monext_Payline/js/view/payment/method-renderer/payline-web-payment-rec'
            }
        );
        /** Add view logic here if needed */
        return Component.extend({});
    }
);
