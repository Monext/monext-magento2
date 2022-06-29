
define(
    [
        'mage/url'
    ],
    function (urlBuilder) {
        'use strict';

        return function (urlPath) {
            window.location.replace(urlBuilder.build(urlPath));
        };
    }
);
