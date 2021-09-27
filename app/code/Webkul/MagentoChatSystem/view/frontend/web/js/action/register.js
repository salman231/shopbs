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
        'Magento_Ui/js/model/messageList',
        'Webkul_MagentoChatSystem/js/action/start-chat',
        'Webkul_MagentoChatSystem/js/model/reply',
        'Webkul_MagentoChatSystem/js/action/assign-chat',
        'Magento_Ui/js/modal/alert'
    ],
    function ($, storage, globalMessageList, startChatAction, replyModel, assignChatAction, alert) {
        'use strict';
        var callbacks = [],
            action = function (
                registerData,
                redirectUrl,
                isGlobal,
                showLoader,
                canChat,
                isLoogedIn
            ) {
                var messageContainer = globalMessageList;
                showLoader(true);
                return storage.post(
                    'chatsystem/customer/createpost',
                    JSON.stringify(registerData),
                    isGlobal
                ).done(function (response) {
                    if (response.errors) {
                        alert({
                            title: 'Error!',
                            content: response.message,
                            actions: {
                                always: function () {}
                            }
                        });
                    } else {
                        registerData.agent_id = 0;
                        registerData.agent_unique_id = '';
                        startChatAction(registerData, canChat).always(function (data) {
                            $('body').trigger('processStart');
                            var data = $.parseJSON(data);
                            registerData.unique_id = data.unique_id;
                            registerData.customer_id = data.customer_id;
                            registerData.status = data.chat_status;
                            replyModel.customerId(data.customer_id);
                            replyModel.customerName(data.customer_name);
                            replyModel.customerEmail(data.customer_email);
                            replyModel.customerUniqueId(data.unique_id);
                            if (data.alreadyAssigned === true) {
                                replyModel.receiverUniqueId(data.agent_unique_id);
                                replyModel.receiverId(data.agent_id);
                                replyModel.receiverName(data.agent_name);
                                replyModel.receiverEmail(data.email);
                                replyModel.clientReply(registerData);
                                replyModel.clientStatusChange(1);
                            }
                            replyModel.profileImageUrl(data.profileImageUrl);
                            if (data.alreadyAssigned === false) {
                                assignChatAction(registerData, canChat).then(function () {
                                    replyModel.clientReply(registerData);
                                    replyModel.clientStatusChange(1);
                                });
                                isLoogedIn(true);
                                $('body').on('newCustomerMessageSumbit', function() {
                                    location.reload();
                                });
                            } else {
                                isLoogedIn(true);
                                $('body').on('newCustomerMessageSumbit', function() {
                                    location.reload();
                                });
                            }
                        });
                        isLoogedIn(true);
                    }
                }).fail(function () {
                    messageContainer.addErrorMessage({
                        'message': 'Could not register. Please try again later'
                    });
                    callbacks.forEach(function (callback) {
                        callback(registerData);
                    });
                });
            };

        action.registerLoginCallback = function (callback) {
            callbacks.push(callback);
        };

        return action;
    }
);