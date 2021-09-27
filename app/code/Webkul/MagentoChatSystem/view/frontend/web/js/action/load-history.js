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
        'Webkul_MagentoChatSystem/js/model/reply',
    ],
    function ($, storage, replyModel) {
        'use strict';

        return function (historyData) {
            var serviceUrl,
                payload;
            /**
             * Checkout for guest and registered customer.
             */
            serviceUrl = 'rest/V1/chat/load-history';
            payload = {
                currentPage: historyData.currentPage,
                customerId: historyData.customerId
            };
            return storage.post(
                serviceUrl,
                JSON.stringify(payload)
            ).fail(
                function (response) {
                    location.reload();
                }
            ).done(
                function (response) {
                    var data = $.parseJSON(response);
                    $.each(data.messages, function (i, v) {
                        var sender = 'admin';
                        var name = 'Support';
                        if (historyData.uniqueId === v.sender_unique_id) {
                            sender = 'customer'
                            name = window.chatboxConfig.customerData.firstname + ' '
                                + window.chatboxConfig.customerData.lastname;
                        }
                        var replyData = {
                            customerName: name,
                            message: v.message,
                            time: v.time,
                            date: v.date,
                            sender: sender,
                            changeDate: v.changeDate
                        };
                        replyModel.setChatHistory(replyData);
                    });
                }
            );
        };
    }
);
