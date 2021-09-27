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
        'Webkul_MagentoChatSystem/js/modal/reply',
        'mage/template',
    ],
    function ($, storage, replyModel, mageTemplate) {
        'use strict';

        return function (historyData) {
            var serviceUrl,
                payload;
            /**
             * Checkout for guest and registered customer.
             */
            serviceUrl = window.chatboxConfig.AdminloadMsgUrl;
            payload = {
                loadtime: historyData.loadtime,
                customerId: historyData.customerId,
                adminId: window.chatboxConfig.adminData.id,
                agentUniqueId: window.chatboxConfig.adminData.agent_unique_id
            };
            var resTmpl = mageTemplate('#reply_admin_template');
            var repTmpl = mageTemplate('#reply_client_template');
            return $.ajax({
                url: serviceUrl,
                data: {
                    formData: payload,
                    form_key: window.FORM_KEY
                },
                type: 'post',
                success: function (response) {
                    if (response.error === false) {
                        $('#chat-history-' + historyData.customerId).html('');
                        $.each(response.message_data.messages, function (i, v) {
                            var sender = 'admin';
                            var name = 'Admin';
                            var replyData = {
                                adminName: name,
                                adminImage: window.chatboxConfig.adminImage,
                                message: v.message,
                                time: v.time,
                                date: v.date,
                                type: v.type,
                                sender: sender
                            };
                            if (historyData.uniqueId === v.sender_unique_id) {
                                sender = 'customer'
                                name = response.message_data.customer_name;
                                var replyData = {
                                    customerName: name,
                                    image: response.message_data.profileImageUrl,
                                    message: v.message,
                                    time: v.time,
                                    date: v.date,
                                    type: v.type,
                                    sender: sender
                                };
                            }

                            if (sender == 'customer') {
                                var resTmpl = mageTemplate('#reply_admin_template');
                                var data = {},
                                    resTmplate;

                                if (replyData !== 'undefined') {
                                    resTmplate = resTmpl({
                                        data: replyData
                                    });
                                    $(resTmplate)
                                        .appendTo($('#chat-history-' + historyData.customerId));
                                }
                            }
                            if (sender == 'admin') {
                                var repTmpl = mageTemplate('#reply_client_template');
                                var data = {},
                                    repTmplate;

                                if (replyData !== 'undefined') {
                                    repTmplate = repTmpl({
                                        data: replyData
                                    });
                                    $(repTmplate)
                                        .appendTo($('#chat-history-' + historyData.customerId));
                                }
                            }
                        });
                        if ($("#chatbox-component div#chat-history-" + historyData.customerId).length) {
                            $("#chatbox-component div#chat-history-" + historyData.customerId).animate({
                                scrollTop: $("#chatbox-component div#chat-history-" + historyData.customerId)[0].scrollHeight
                            });
                        }
                    }
                },
                error: function (response) {
                    console.log(response.message);
                }
            });
        };
    }
);