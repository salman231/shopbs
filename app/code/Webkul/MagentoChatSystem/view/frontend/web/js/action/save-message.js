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
        'Webkul_MagentoChatSystem/js/model/url-builder',
        'mage/storage',
        'Magento_Ui/js/modal/alert',
        'Webkul_MagentoChatSystem/js/model/reply',
        'Webkul_MagentoChatSystem/js/model/manage-rating'
    ],
    function ($, urlBuilder, storage, alert, replyModel, ratingModel) {
        'use strict';

        return function (messageData) {
            var serviceUrl,
                payload;
            /**
             * Checkout for guest and registered customer.
             */
            serviceUrl = 'rest/V1/message/save-message';
            payload = {
                senderId: messageData.customer_id,
                receiverId: messageData.receiver_id,
                receiverUniqueId: messageData.receiver_unique_id,
                message: messageData.message,
                dateTime: messageData.dateTime,
                msgType: messageData.type
            };

            return storage.post(
                serviceUrl,
                JSON.stringify(payload)
            ).fail(
                function (response) {
                    if (response.status == 401) {
                        location.reload();
                    }
                }
            );
        };
    }
);