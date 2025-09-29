function camalize(str) {
    return str.toLowerCase().replace(/[^a-zA-Z0-9]+(.)/g, (m, chr) => chr.toUpperCase());
}
const WidgetApi = {
    widgetContext : {},

    setContext: function(config) {
        this.widgetContext.environment = config['environment'] ?? 'HOMO';
        this.widgetContext.widgetDisplay = config['widgetDisplay'] ?? 'tab';
        this.widgetContext.containerId = config['containerId'] ?? 'payline-widget-container';
        this.widgetContext.dataEmbeddedredirectionallowed = config['dataEmbeddedredirectionallowed'] ?? 'true';
        this.widgetContext.nonce = config['nonce'] ?? false;
        if(config['widgetCustomization']) {
            this.widgetContext.ctaLabel = config['widgetCustomization']['widget_cta_label'] ?? false;
            this.widgetContext.ctaTextUnder = config['widgetCustomization']['widget_cta_text_under'] ?? false;
        }
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
        const newScript = document.createElement('script');
        newScript.setAttribute('id', 'payline-widget-api-js');
        newScript.setAttribute('type', 'text/javascript');
        newScript.setAttribute('src', 'https://' + this.getDomain() + '/scripts/widget-min.js');
        document.head.appendChild(newScript);
    },

    initCss: function () {
        const newStyle = document.createElement('link');
        newStyle.setAttribute('rel', 'stylesheet');
        newStyle.setAttribute('type', 'text/css');
        newStyle.setAttribute('href', 'https://' + this.getDomain() + '/styles/widget-min.css');
        document.head.appendChild(newStyle);
    },

    destroyJs: function () {
        // avoid conflict with .remove() method of jQuery
        const nodeToDelete = document.getElementById('payline-widget-api-js');
        if (nodeToDelete && nodeToDelete.parentNode) {
            nodeToDelete.parentNode.removeChild(nodeToDelete);
        }
    },


    showWidget: function (context, dataToken) {
        this.setContext(context);

        let widgetDivAttributes = [];
        widgetDivAttributes.push('data-token="' + dataToken + '"');
        widgetDivAttributes.push('data-template="' + this.getContext('widgetDisplay') + '"');
        widgetDivAttributes.push('data-embeddedredirectionallowed="' + this.getContext('dataEmbeddedredirectionallowed') + '"');
        if( this.getContext('nonce')) {
            widgetDivAttributes.push('data-cspnonce="' + this.getContext('nonce') + '"');
        }

        let callbacks = [
            // 'event-willinit',
            // 'event-willshow',
            'event-finalstatehasbeenreached',
            'event-didshowstate',
            // 'event-willdisplaymessage',
            // 'event-willremovemessage',
            // 'event-beforepayment',
        ];

        callbacks.forEach(callback => {
            widgetDivAttributes.push('data-' + callback + '="' + camalize(callback) + '"');
        });

        let paylineWidgetHtml = `<div id="PaylineWidget" ${widgetDivAttributes.join(' ')} />`;

        const widgetContainerElement = document.getElementById(this.getContext('containerId'));
        if (widgetContainerElement) {
            widgetContainerElement.innerHTML += paylineWidgetHtml;
        }


        if (!window.isPaylineWidgetCssApiLoaded) {
            this.initCss();
            window.isPaylineWidgetCssApiLoaded = true;
        }

        this.initJs();
    },

    destroyWidget: function (widgetContainerId) {
        this.destroyJs();
        const widgetContainerElement = document.getElementById(widgetContainerId);
        if (widgetContainerElement) {
            widgetContainerElement.innerHTML = '';
        }
    },

    resetWidget: function (dataToken) {
        Payline.Api.reset(dataToken)
    },

    customizeWidget: function () {

        const ctaLabel = this.getContext('ctaLabel');
        const ctaTextUnder = this.getContext('ctaTextUnder');
        if (ctaLabel) {
            document.querySelectorAll('.PaylineWidget .pl-pay-btn, .PaylineWidget .pl-btn').forEach(paylineCTA => {
                paylineCTA.innerText = ctaLabel.replace("{{amount}}", Payline.Api.getContextInfo("PaylineFormattedAmount"));
            });
        }

        if (ctaTextUnder) {
            document.querySelectorAll('.PaylineWidget .pl-pay-btn, .PaylineWidget .pl-btn').forEach(function(btn) {
                const p = document.createElement('p');
                p.innerHTML = ctaTextUnder;
                p.classList.add('pl-text-under-cta');
                btn.parentNode.insertBefore(p, btn.nextSibling);
            });
        }
    }
};

if (typeof define === "function") {
    define(
        [],
        function () {
            'use strict';
            return WidgetApi;
        }
    );
}



