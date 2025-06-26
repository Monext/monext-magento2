define(
    [
        'jquery',
        'underscore',
        'Monext_Payline/js/widget-api-vanilla'
    ],
    function ($, _, WidgetApi) {
        'use strict';

        window.eventDidshowstate= function (e) {
            if(e.state === 'PAYMENT_METHODS_LIST') {
                $(document.body).trigger('processStop');
                WidgetApi.customizeWidget();
            }
        };

        window.eventFinalstatehasbeenreached = function (e) {
            $(document.body).trigger('processStart');
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
