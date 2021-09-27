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
    [
        'jquery',
        'mage/storage',
        'mage/url'
    ],
    function ($, storage, urlBuilder) {
        'use strict';

        return function (statusData) {
            var serviceUrl,
                payload;

            /**
             * Checkout for guest and registered customer.
             */
            payload = {
                adminId: window.chatboxConfig.adminData.id,
                status: statusData.status,
            };

            return $.ajax({
                url : window.chatboxConfig.adminUpdateChatUrl,
                data : {formData: payload, form_key: window.FORM_KEY},
                type : 'post',
                success: function (response) {
                    if (response.error === false) {
                    }
                },
                error: function (response) {
                    console.log(response.message);
                }
            });
        };
    }
);
