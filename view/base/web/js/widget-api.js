define(
    [
        'jquery',
        'underscore',
    ],
    function ($, _) {
        'use strict';

        /*
        window.paylineEventManager = {
            eventWillinit: function () {
                console.log('eventWillinit');
                console.log(arguments);
            },
            eventWillshow: function () {
                console.log('eventWillshow');
                console.log(arguments);
            },
            eventFinalstatehasbeenreached: function () {
                console.log('eventFinalstatehasbeenreached');
                console.log(arguments);
            },
        };
        */

        window.eventWillinit= function () {
            console.log('eventWillinit');
            console.log(arguments);
        };

        window.eventWillshow= function () {
            console.log('eventWillshow');
            console.log(arguments);
        };

        window.eventFinalstatehasbeenreached= function () {
            console.log('eventFinalstatehasbeenreached');
            console.log(arguments);
            require('Magento_Customer/js/customer-data').invalidate(['cart']);
            console.log('after invalidate cart');

        };

        var WidgetApi = {};

        _.extend(WidgetApi, {
            initJs: function (environment) {
                if (environment === 'PROD') {
                    $('head').append('<script id="payline-widget-api-js" type="text/javascript" src="https://payment.payline.com/scripts/widget-min.js"></script>');
                } else {
                    $('head').append('<script id="payline-widget-api-js" type="text/javascript" src="https://homologation-payment.payline.com/scripts/widget-min.js"></script>');
                }
            },

            initCss: function (environment) {
                if (environment === 'PROD') {
                    $('head').append('<link rel="stylesheet" type="text/css" href="https://payment.payline.com/styles/widget-min.css">');
                } else {
                    $('head').append('<link rel="stylesheet" type="text/css" href="https://homologation-payment.payline.com/styles/widget-min.css">');
                }
            },

            destroyJs: function () {
                $('#payline-widget-api-js').remove();
            },

            finalStateReached: function(state) {
                console.log(state)
            },

            showWidget: function (environment, dataToken, dataColumn, widgetContainerId) {
                var paylineWidgetHtml = '';
                var callbacks = [
                    // 'embeddedredirectionallowed',
                    // 'event-willinit',
                    // 'event-willshow',
                    'event-finalstatehasbeenreached',
                    //'event-didshowstate',
                    // 'event-willdisplaymessage',
                    // 'event-willremovemessage',
                    // 'event-beforepayment'
                ];

                var callbacksByEvents = [];
                $.each(callbacks, function(i, item) {
                    //callbacksByEvents.push('data-' + item + '="paylineEventManager.' +jQuery.camelCase(item) + '"');
                    callbacksByEvents.push('data-' + item + '="' +jQuery.camelCase(item) + '"');
                });

                if (dataColumn === 'lightbox') {
                    paylineWidgetHtml = '<div id="PaylineWidget" data-token="' +
                        dataToken
                        + '" '
                        + callbacksByEvents.join(' ')
                        + '/>';
                } else {
                    paylineWidgetHtml = '<div id="PaylineWidget" data-template="' +
                        dataColumn +
                        '" data-token="' +
                        dataToken +
                        '" ' +
                        callbacksByEvents.join(' ') +
                        '/>';
                }

                $('#'+widgetContainerId).append(paylineWidgetHtml);

                if (!window.isPaylineWidgetCssApiLoaded) {
                    this.initCss(environment);
                    window.isPaylineWidgetCssApiLoaded = true;
                }

                this.initJs(environment);
            },

            destroyWidget: function (widgetContainerId) {
                this.destroyJs();
                $('#'+widgetContainerId).html('');
            }
        });



        return WidgetApi;
    }
);
