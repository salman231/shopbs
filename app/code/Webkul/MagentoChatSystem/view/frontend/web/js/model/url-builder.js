/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MagentoChatSystem
 * @author    Webkul
 * @copyright Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
/*jshint browser:true jquery:true*/
/*global alert*/
define(
    ['jquery'],
    function ($) {
        return {
            method: "rest",
            storeCode: window.chatboxConfig.storeCode,
            version: 'V1',
            serviceUrl: ':method/:storeCode/:version',
            /**
             * Create Url
             */
            createUrl: function (url, params) {
                var completeUrl = this.serviceUrl + url;
                return this.bindParams(completeUrl, params);
            },
            /**
             * Bind Params
             */
            bindParams: function (url, params) {
                params.method = this.method;
                params.storeCode = this.storeCode;
                params.version = this.version;

                var urlParts = url.split("/");
                urlParts = urlParts.filter(Boolean);

                $.each(urlParts, function (key, part) {
                    part = part.replace(':', '');
                    if (params[part] != undefined) {
                        urlParts[key] = params[part];
                    }
                });
                return urlParts.join('/');
            }
        };
    }
);