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

        window.eventFinalstatehasbeenreached = function (e) {
            fullScreenLoader.startLoader();
            if ( e.state === "PAYMENT_SUCCESS" ) {
                //--> Redirect to success page
                //--> Ticket is hidden by CSS
                //--> Wait for DOM update ti simulate a click on the ticket confirmation button
                window.setTimeout(() => {
                    const ticketConfirmationButton = document.getElementById("pl-ticket-default-ticket_btn");
                    if ( ticketConfirmationButton ) {
                        ticketConfirmationButton.click();
                    }
                }, 0);
            }
            require('Magento_Customer/js/customer-data').invalidate(['cart']);
        }

        return WidgetApi;
    }
);
