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
        'ko',
        'underscore',
        'Webkul_MagentoChatSystem/js/emoji.min',
        'Webkul_MagentoChatSystem/js/bin/push.min',
        'Webkul_MagentoChatSystem/js/client'
    ],
    function (ko, _, emojify, push, SocketIOFileUpload) {
        'use strict';

        return {
            clientReply: ko.observable(),
            clientStatusChange: ko.observable(),
            adminStatus: ko.observable(window.chatboxConfig.isAdminLoggedIn),
            adminResponse: ko.observable(),
            chatHistory: ko.observableArray(),
            profileImageUrl: ko.observable(window.chatboxConfig.customerData.profileImageUrl),
            notificationSound: ko.observable(),
            socketObject: null,
            receiverUniqueId: ko.observable(window.chatboxConfig.receiverUniqueId),
            receiverId: ko.observable(window.chatboxConfig.receiverId),
            receiverName: ko.observable(window.chatboxConfig.agentData.agent_name),
            receiverEmail: ko.observable(window.chatboxConfig.agentData.agent_email),
            customerId: ko.observable(window.chatboxConfig.customerData.id),
            customerUniqueId: ko.observable(window.chatboxConfig.customerData.uniqueId),
            customerName: ko.observable(
                window.chatboxConfig.customerData.firstname +
                ' ' +
                window.chatboxConfig.customerData.lastname
            ),
            customerEmail: ko.observable(window.chatboxConfig.customerData.email),
            loadingState: ko.observable(''),
            showLoader: ko.observable(false),
            agentGoesOff: ko.observable(false),
            agentGoesOffError: ko.observable(''),
            scrollDown: ko.observable(false),
            /**
             * Get Socket File Upload
             */
            getSocketFileUpload: function () {
                return new SocketIOFileUpload(this.getSocketObject());
            },

            /**
             * @return {Function}
             */
            getReply: function () {
                return this.clientReply();
            },
            /**
             * @return {Function}
             */
            setReply: function (reply) {
                return this.clientReply(reply);
            },

            /**
             * @return {Function}
             */
            getResponse: function () {
                return this.adminResponse();
            },
            /**
             * @return {Function}
             */
            setResponse: function (response) {
                return this.adminResponse(response);
            },
            /**
             * Get Socket Object
             */
            getSocketObject: function () {
                return this.socketObject;
            },
            /**
             * Set Socket Object
             */
            setSocketObject: function (socket) {
                this.socketObject = socket;
            },
            /**
             * Get Chat History
             */
            getChatHistory: function () {
                return this.chatHistory();
            },
            /**
             * Set Chat History
             */
            setChatHistory: function (object) {
                this.chatHistory.push(object);
            },
            /**
             * Set Scroll Down Page
             */
            setScrollDownPage: function (value) {
                this.scrollDown(true);
            },
            /**
             * Get Customer Name
             */
            getCustomerName: function () {
                return this.customerName();
            },
            /**
             * Set Admin Status
             * @param {*} status 
             */
            setAdminStatus: function(status) {
                this.adminStatus(status);
            },
            /**
             * Update variable content.
             *
             * @param {Object} updatedChat
             * @returns void
             */
            update: function (updatedChat) {
                this.adminStatus(updatedChat.isAdminLoggedIn);
                if (!_.isUndefined(updatedChat.customerData) &&
                    updatedChat.customerData.hasOwnProperty('profileImageUrl')
                ) {
                    this.profileImageUrl(updatedChat.customerData.profileImageUrl);
                    this.customerId(updatedChat.customerData.id);
                    this.customerUniqueId(updatedChat.customerData.uniqueId);
                    this.customerName(
                        updatedChat.customerData.firstname +
                        ' ' +
                        updatedChat.customerData.lastname
                    );
                    this.customerEmail(updatedChat.customerData.email);
                }

                this.receiverUniqueId(updatedChat.receiverUniqueId);
                this.receiverId(updatedChat.receiverId);
                if (!_.isUndefined(updatedChat.agentData)) {
                    this.receiverName(updatedChat.agentData.agent_name);
                    this.receiverEmail(updatedChat.agentData.agent_email);
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
             * Call Emojify
             */
            callEmojify: function (id) {
                emojify.setConfig({
                    tag_type: 'img', // Only run emojify.js on this element
                    only_crawl_id: id, // Use to restrict where emojify.js applies
                    img_dir: window.chatboxConfig.emojiImagesPath, // Directory for emoji images
                    ignored_tags: { // Ignore the following tags
                        'SCRIPT': 1,
                        'TEXTAREA': 1,
                        'A': 1,
                        'PRE': 1,
                        'CODE': 1
                    }
                });
                emojify.run();
            },
            /**
             * Show Notification
             */
            showNotification: function (data) {
                var self = this,
                    message = '';

                push.Permission.request(onGranted, onDenied);

                function onGranted()
                {
                    if (data.type == 'text') {
                        push.create(data.adminName, {
                            title: '',
                            body: data.message,
                            icon: data.image,
                            tag: 'notification' + data.adminUniqueId,
                            timeout: 8000,
                            onClick: function () {
                                window.focus();
                                this.close();
                            },
                        });
                    } else {
                        push.create(data.adminName, {
                            title: '',
                            body: data.filename,
                            icon: data.image,
                            tag: 'notification' + data.adminUniqueId,
                            timeout: 8000,
                            onClick: function () {
                                window.focus();
                                this.close();
                            },
                        });
                    }
                }

                function onDenied(status)
                {
                    console.log('Notification permission status: denied');
                }
            }
        };
    },
);