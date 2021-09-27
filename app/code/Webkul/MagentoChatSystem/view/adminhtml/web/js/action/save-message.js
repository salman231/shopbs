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

        return function (messageData) {
            var serviceUrl,
                payload;

            /**
             * Checkout for guest and registered customer.
             */
            payload = {
                senderId: messageData.customer_id,
                senderUniqueId: messageData.agent_unique_id,
                receiverId: messageData.receiver_id,
                message: messageData.message,
                dateTime: messageData.dateTime,
                msg_type: messageData.msg_type
            };

            return $.ajax({
                url: window.chatboxConfig.adminBaseUrl,
                data: {
                    formData: payload,
                    form_key: window.FORM_KEY
                },
                type: 'post',
                error: function (response) {
                    console.log(response.message);
                }
            });
        };
    }
);