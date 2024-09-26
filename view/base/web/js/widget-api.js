define(
    [
        'jquery',
        'underscore',
        'Magento_Checkout/js/model/full-screen-loader'
    ],
    function ($, _, fullScreenLoader) {
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

        let WidgetApi = {};
        _.extend(WidgetApi, {

            widgetContext : {},

            setContext: function(config) {
                this.widgetContext['environment'] = config.hasOwnProperty('environment') ? config['environment'] : 'HOMO';
                this.widgetContext['widgetDisplay'] = config.hasOwnProperty('widgetDisplay') ? config['widgetDisplay'] : 'tab';
                this.widgetContext['containerId'] = config.hasOwnProperty('containerId') ? config['containerId'] : 'payline-widget-container';
                this.widgetContext['dataEmbeddedredirectionallowed'] = config.hasOwnProperty('dataEmbeddedredirectionallowed') ? config['dataEmbeddedredirectionallowed'] : 'true';
            },

            getContext: function (key) {
                if(!key || !this.widgetContext.hasOwnProperty(key)) {
                    throw new Error('Cannot get key for context');
                }

                return this.widgetContext[key];
            },

            getDomain : function () {
                return (this.getContext('environment') === 'PROD') ? 'payment.payline.com' : 'homologation-payment.payline.com';
            },

            initJs: function () {
                $('head').append('<script id="payline-widget-api-js" type="text/javascript" src="https://'+this.getDomain() +'/scripts/widget-min.js"></script>');
            },

            initCss: function () {
                $('head').append('<link rel="stylesheet" type="text/css" href="https://'+this.getDomain() +'/styles/widget-min.css">');
            },

            destroyJs: function () {
                $('#payline-widget-api-js').remove();
            },

            finalStateReached: function(state) {
                console.log(state)
            },


            showWidget: function (context, dataToken) {
                this.setContext(context);

                let widgetDivAttributes = [];
                widgetDivAttributes.push('data-token="' + dataToken + '"');
                widgetDivAttributes.push('data-template="' + this.getContext('widgetDisplay') + '"');
                widgetDivAttributes.push('data-embeddedredirectionallowed="' + this.getContext('dataEmbeddedredirectionallowed') + '"');


                let callbacks = [
                    // 'event-willinit',
                    // 'event-willshow',
                    'event-finalstatehasbeenreached',
                    'event-didshowstate',
                    // 'event-willdisplaymessage',
                    // 'event-willremovemessage',
                    // 'event-beforepayment',
                ];

                $.each(callbacks, function(i, item) {
                    widgetDivAttributes.push('data-' + item + '="' +jQuery.camelCase(item) + '"');
                });

                let paylineWidgetHtml = `<div id="PaylineWidget" ${widgetDivAttributes.join(' ')} />`;

                $('#'+this.getContext('containerId')).append(paylineWidgetHtml);

                if (!window.isPaylineWidgetCssApiLoaded) {
                    this.initCss();
                    window.isPaylineWidgetCssApiLoaded = true;
                }

                this.initJs();
            },

            destroyWidget: function (widgetContainerId) {
                this.destroyJs();
                $('#'+widgetContainerId).html('');
            }
        });

        return WidgetApi;
    }
);
