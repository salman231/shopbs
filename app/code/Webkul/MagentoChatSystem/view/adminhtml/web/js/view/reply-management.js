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
define([
    'jquery',
    'mage/template',
    'uiComponent',
    'mage/validation',
    'ko',
    'Webkul_MagentoChatSystem/js/modal/reply',
    'Webkul_MagentoChatSystem/js/chatbox',
    'Webkul_MagentoChatSystem/js/socket.io',
    'Webkul_MagentoChatSystem/js/action/save-message'
], function ($, mageTemplate, Component, validation, ko, replyModel, chatbox, io, saveMessageAction) {
    'use strict';
    return Component.extend({
        options: {},

        initialize: function () {
            var self = this;
            this._super();
            this.resTmpl = mageTemplate('#reply_admin_template');
            this.repTmpl = mageTemplate('#reply_client_template');
            this.notifyTmpl = mageTemplate('#notification-template');
            this.chatTmpl = mageTemplate('#chat_window_template');
            replyModel.adminReply.subscribe(function (adminReply) {
                self._createReplyData(adminReply);
            });
            /**
             * if customer message arrived
             */
            replyModel.getResponse().subscribe(function (response) {
                self._createResponseData(response);
            });

            /**
             * if customer change his chat status
             */
            replyModel.customerStatus.subscribe(function (status) {
                self._updateClientStatus(status[0]);
            });

            /**
             * send customer that admin change his status.
             */
            replyModel.adminStatusChange.subscribe(function (status) {
                self.sendStatusUpdateSignal(status);
            });
            replyModel.callEmojify('chatbox-component');
        },
        /**
         * Create Reply Data
         */
        _createReplyData: function (adminReply) {
            var name = window.chatboxConfig.adminData.name,
                self = this;
            var clientData = {
                sender: 'admin',
                adminId: window.chatboxConfig.adminData.id,
                adminUniqueId: window.chatboxConfig.adminData.agent_unique_id,
                adminName: window.chatboxConfig.adminData.name,
                adminImage: window.chatboxConfig.adminImage,
                message: adminReply.reply,
                isSuperAdmin: window.chatboxConfig.isSuperAdmin,
                time: this.getFormateTime(),
                date: this.getDate(),
                receiver: adminReply.customerId,
                type: adminReply.type
            }
            if ($.trim(clientData.message.replace(/[<]br[^>]*[>]/gi, "")).length) {
                var saveMsgData = {
                    customer_id: window.chatboxConfig.adminData.id,
                    agent_unique_id: window.chatboxConfig.adminData.agent_unique_id,
                    receiver_id: adminReply.customerId,
                    isSuperAdmin: window.chatboxConfig.isSuperAdmin,
                    message: adminReply.reply,
                    dateTime: this.getDate() + this.getTime(),
                    msg_type: adminReply.type
                }
                var statusError = 0;
                $.each(replyModel.customerStatus(), function (i, v) {
                    if (adminReply.customerId == v.customerId && v.status == 0) {
                        statusError = 1;
                    }
                });

                if (statusError == 0) {
                    if (clientData.type != 'text') {
                        saveMessageAction(saveMsgData).success(function (response) {
                            clientData.message = response.message;
                            clientData.filename = saveMsgData.message;
                            self._sendNewMessage(clientData);
                        });
                    } else {
                        self._sendNewMessage(clientData);
                        saveMessageAction(saveMsgData);
                    }
                }
            }
            replyModel.callEmojify('chatbox-component');
        },
        /**
         * Send New Message
         */
        _sendNewMessage: function (reply) {
            var socket = replyModel.getSocketObject();
            var data = {},
                repTmpl;

            if (data !== 'undefined') {
                var statusError = 0;
                $.each(replyModel.customerStatus(), function (i, v) {
                    if (reply.receiver == v.customerId && v.chat_status == 0) {
                        statusError = 1;
                    }
                });
                reply.statusError = statusError;
                repTmpl = this.repTmpl({
                    data: reply
                });
                $(repTmpl)
                    .appendTo($('#chat-history-' + reply.receiver));
            }
            $("div#chat-history-" + reply.receiver).animate({
                scrollTop: $("#chatbox-component div#chat-history-" + reply.receiver)[0].scrollHeight
            });
            if (statusError == 0) {
                socket.emit('newAdminMessageSumbit', reply);
            }
        },
        /**
         * Create Response Data
         */
        _createResponseData: function (response) {
            let self = this;
            var data = {},
                resTmpl,
                notifyTmpl;
            if (response !== 'undefined') {
                if (response.image == '' || response.image == 'undefined') {
                    response.image = window.chatboxConfig.defaultImageUrl;
                }
                resTmpl = this.resTmpl({
                    data: response
                });
                $(resTmpl)
                    .appendTo($('#chat-history-' + response.customerId));
                if (response.receiver == window.chatboxConfig.adminData.id) {
                    self._updateClientStatus({'status': response.chat_status , 'customerId': response.customerId });
                    replyModel.showNotification(response);
                }
                setTimeout(function () {
                    $('body').find('.chat-message-notification').fadeOut('slow', function () {
                        $('body').find('.chat-message-notification').remove();
                    });

                }, 5000);
                var playPromise = $('#chatbox-component').find('#myAudio').get(0).play();
                if (playPromise !== undefined) {
                    playPromise.then(_ => {
                        // Automatic playback started!
                        // Show playing UI.
                    }).catch(error => {
                        // Auto-play was prevented
                        // Show paused UI.
                    });
                }
            }
            replyModel.callEmojify('chatbox-component');
        },
        /**
         * Blink Tab
         */
        blinkTab: function (message) {
            var oldTitle = document.title,
                timeoutId,
                blink = function () {
                    document.title = document.title == message ? ' ' : message;
                },
                clear = function () {
                    clearInterval(timeoutId);
                    document.title = oldTitle;
                    window.onmousemove = null;
                    timeoutId = null;
                };

            if (!timeoutId) {
                timeoutId = setInterval(blink, 1000);
                window.onmousemove = clear;
            }
        },
        /**
         * Send Status Update Signal
         */
        sendStatusUpdateSignal: function (status) {
            var statusData = {
                sender: 'admin',
                receiverData: window.enableChatUsersConfig.enableUserData,
                status: status
            }
            var socket = replyModel.getSocketObject();
            socket.emit('admin status changed', statusData);
        },
        /**
         * Open Chat Window
         */
        openChatWindow: function (selectedUser) {
            var chatTmplate = mageTemplate('#chat_window_template');
            if ($('#chatbox-component').find('#live-chat-' + selectedUser.customerId).length === 0) {
                if (replyModel.openWindowCount() == 2) {
                    var openChats = $('body').find('[id^="live-chat-"]');
                    openChats[0].remove();
                    $('[id^="live-chat-"]').css('right', (20) + 'px');
                    replyModel.openWindowCount(replyModel.openWindowCount() - 1);
                }
                if (replyModel.openWindowCount() < 2) {
                    var cssclass = '';
                    if (selectedUser.chat_status == 1) {
                        cssclass = 'active';
                    } else if (selectedUser.chat_status == 2) {
                        cssclass = 'busy';
                    } else if (selectedUser.chat_status == 0) {
                        cssclass = 'offline';
                    }
                    var data = {
                            customerId: selectedUser.customerId,
                            name: selectedUser.customerName,
                            chat_status: selectedUser.chat_status,
                            class: cssclass,
                            image: selectedUser.image,
                        },
                        chatTmpl;
                    if (data !== 'undefined') {
                        chatTmpl = chatTmplate({
                            data: data
                        });
                        $(chatTmpl)
                            .appendTo($('#chatbox-component'));

                        ko.applyBindings(
                            chatbox(
                                selectedUser.customerId
                            ),
                            document.getElementById("live-chat-" + selectedUser.customerId)
                        );
                    }
                    if (replyModel.openWindowCount() > 0) {
                        $('#live-chat-' + selectedUser.customerId).css('right', (300 * replyModel.openWindowCount() + 30) + 'px');
                    }
                    replyModel.openWindowCount(replyModel.openWindowCount() + 1);
                    replyModel.callEmojify('chatbox-component');
                } else {
                    alert({
                        title: 'Attension!',
                        content: 'Maximum two chat winodw can be open.',
                        actions: {
                            always: function () {}
                        }
                    });
                }
            }
        },
        /**
         * Update Client Status
         */
        _updateClientStatus: function (status) {
            if (status.status == 1) {
                $('.active-users-model #client_' + status.customerId + ' .user-status').removeClass('busy');
                $('.active-users-model #client_' + status.customerId + ' .user-status').removeClass('offline');
                $('.active-users-model #client_' + status.customerId + ' .user-status').addClass('active');

                $('#live-chat-' + status.customerId + ' .user-status').removeClass('busy');
                $('#live-chat-' + status.customerId + ' .user-status').removeClass('offline');
                $('#live-chat-' + status.customerId + ' .user-status').addClass('active');
            }
            if (status.status == 2) {
                $('.active-users-model #client_' + status.customerId + ' .user-status').removeClass('active');
                $('.active-users-model #client_' + status.customerId + ' .user-status').removeClass('offline');
                $('.active-users-model #client_' + status.customerId + ' .user-status').addClass('busy');

                $('#live-chat-' + status.customerId + ' .user-status').addClass('busy');
                $('#live-chat-' + status.customerId + ' .user-status').removeClass('active');
                $('#live-chat-' + status.customerId + ' .user-status').removeClass('offline');
            }
            if (status.status == 0) {
                $('.active-users-model #client_' + status.customerId + ' .user-status').removeClass('active');
                $('.active-users-model #client_' + status.customerId + ' .user-status').removeClass('busy');
                $('.active-users-model #client_' + status.customerId + ' .user-status').addClass('offline');

                $('#live-chat-' + status.customerId + ' .user-status').addClass('offline');
                $('#live-chat-' + status.customerId + ' .user-status').removeClass('active');
                $('#live-chat-' + status.customerId + ' .user-status').removeClass('busy');
            }
        },
        /**
         * Get Date
         */
        getDate: function () {
            var now = new Date();
            var year = "" + now.getFullYear();
            var month = "" + (now.getMonth() + 1);
            if (month.length == 1) {
                month = "0" + month;
            }
            var day = "" + now.getDate();
            if (day.length == 1) {
                day = "0" + day;
            }
            return year + "-" + month + "-" + day + " ";
        },
        /**
         * Get Time
         */
        getTime: function () {
            var now = new Date();
            var hour = "" + now.getHours();
            if (hour.length == 1) {
                hour = "0" + hour;
            }
            var minute = "" + now.getMinutes();
            if (minute.length == 1) {
                minute = "0" + minute;
            }
            var second = "" + now.getSeconds();
            if (second.length == 1) {
                second = "0" + second;
            }
            return hour + ":" + minute;
        },
        /**
         * Get Formate Time
         */
        getFormateTime: function () {
            var now = new Date();
            var hours = now.getHours() > 12 ? now.getHours() - 12 : now.getHours();
            var am_pm = now.getHours() >= 12 ? "PM" : "AM";
            hours = hours < 10 ? "0" + hours : hours;

            var minute = "" + now.getMinutes();
            if (minute.length == 1) {
                minute = "0" + minute;
            }
            var second = "" + now.getSeconds();
            if (second.length == 1) {
                second = "0" + second;
            }
            return hours + ":" + minute + " " + am_pm;;
        }
    });
});