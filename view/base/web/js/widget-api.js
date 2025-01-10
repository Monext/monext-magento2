define(
    [
        'jquery',
        'underscore',
        'Magento_Checkout/js/model/full-screen-loader',
        'Monext_Payline/js/widget-api-vanilla'
    ],
    function ($, _, fullScreenLoader, WidgetApi) {
        'use strict';

        window.eventDidshowstate= function () {
            if(arguments.state='PAYMENT_SUCCESS') {
                fullScreenLoader.stopLoader();
            }
        };

        window.eventFinalstatehasbeenreached= function (state) {
            fullScreenLoader.startLoader();
            require('Magento_Customer/js/customer-data').invalidate(['cart']);
        };

        return WidgetApi;
    }
);
