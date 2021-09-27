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
        'Webkul_MagentoChatSystem/js/action/assign-chat',
        'Webkul_MagentoChatSystem/js/model/reply',
        'Magento_Ui/js/modal/alert',
        'Magento_Customer/js/customer-data',
        'mage/translate'
    ],
    function (
        $,
        storage,
        globalMessageList,
        startChatAction,
        assignChatAction,
        replyModel,
        alert,
        customerData
    ) {
        'use strict';
        var callbacks = [],
            action = function (loginData, redirectUrl, isGlobal, canChat, showLoader, isLoogedIn) {
                replyModel.loadingState($.mage.__('starting chat...'));
                showLoader(true);
                return storage.post(
                    'customer/ajax/login',
                    JSON.stringify(loginData),
                    isGlobal
                ).done(function (response) {
                    showLoader(false);
                    customerData.reload(['customer'], true);
                    if (response.errors) {
                        alert({
                            title: 'Error!',
                            content: response.message,
                            actions: {
                                always: function () {}
                            }
                        });
                    } else {
                        startChatAction(loginData, canChat).always(function (data) {
                            $('body').trigger('processStart');
                            var data = $.parseJSON(data);
                            loginData.unique_id = data.unique_id;
                            loginData.customer_id = data.customer_id;
                            loginData.status = data.chat_status;
                            replyModel.customerId(data.customer_id);
                            replyModel.customerName(data.customer_name);
                            replyModel.customerEmail(data.customer_email);
                            replyModel.customerUniqueId(data.unique_id);
                            if (data.alreadyAssigned === true) {
                                replyModel.receiverUniqueId(data.agent_unique_id);
                                replyModel.receiverId(data.agent_id);
                                replyModel.receiverName(data.agent_name);
                                replyModel.receiverEmail(data.email);
                                replyModel.clientReply(loginData);
                                replyModel.clientStatusChange(1);
                            }
                            replyModel.profileImageUrl(data.profileImageUrl);
                            if (data.alreadyAssigned === false) {
                                assignChatAction(loginData, canChat).then(function () {
                                    replyModel.clientReply(loginData);
                                    replyModel.clientStatusChange(1);
                                    isLoogedIn(true);
                                    $('body').on('newCustomerMessageSumbit', function() {
                                        location.reload();
                                    });
                                });
                            } else {
                                isLoogedIn(true);
                                $('body').on('newCustomerMessageSumbit', function() {
                                    location.reload();
                                });
                            }
                        });
                    }
                }).fail(function () {
                    alert({
                        title: 'Error!',
                        content: 'Something went wrong!',
                        actions: {
                            always: function () {}
                        }
                    });
                });
            };

        action.registerLoginCallback = function (callback) {
            callbacks.push(callback);
        };

        return action;
    }
);