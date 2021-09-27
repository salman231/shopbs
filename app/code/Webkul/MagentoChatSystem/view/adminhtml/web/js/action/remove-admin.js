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
        'Webkul_MagentoChatSystem/js/modal/reply'
    ],
    function ($, storage, replyModel) {
        'use strict';

        return function (removeData) {
            var serviceUrl,
                payload;
            /**
             * Checkout for guest and registered customer.
             */
            serviceUrl = window.chatboxConfig.removeAssignedChatUrl;
            payload = removeData;
            return $.ajax({
                url : serviceUrl,
                data : {formData: payload, form_key: window.FORM_KEY},
                type : 'post',
                success: function (response) {
                    
                },
                error: function (response) {
                    console.log(response.message);
                }
            });
        };
    }
);
