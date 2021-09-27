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
        'Webkul_MagentoChatSystem/js/emoji.min',
        'Webkul_MagentoChatSystem/js/bin/push.min',
        'Webkul_MagentoChatSystem/js/client'
    ],
    function (ko, emojify, push, SocketIOFileUpload) {
        'use strict';
        var totalChatWindows = ko.observable();
        var chatWindows = ko.observableArray();
        return {
            adminReply: ko.observable(),
            customerResponse: ko.observable(),
            refreshChatModel: ko.observable(),
            customerStatus: ko.observable(window.enableChatUsersConfig.enableUserData),
            adminStatusChange: ko.observable(window.chatboxConfig.adminData.status),
            socketObject: ko.observable(null),
            openWindowCount: ko.observable(0),
            usersList: ko.observableArray([]),

            getSocketFileUpload: function () {
                return new SocketIOFileUpload(this.getSocketObject());
            },

            /**
             * @return {Function}
             */
            getReply: function () {
                return this.adminReply();
            },
            /**
             * @return {Function}
             */
            setReply: function (reply) {
                return this.adminReply(reply);
            },

            /**
             * @return {Function}
             */
            getResponse: function () {
                return this.customerResponse;
            },
            /**
             * @return {Function}
             */
            setResponse: function (response) {
                return this.customerResponse(response);
            },

            setRefreshList: function (data) {
                return this.refreshChatModel(data);
            },

            getSocketObject: function () {
                return this.socketObject();
            },

            setSocketObject: function (socket) {
                this.socketObject(socket);
            },
            setCustomerStatus: function (response) {
                this.customerStatus(response);
            },

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

            showNotification: function (data) {
                var self = this,
                    message = '';

                push.Permission.request(onGranted, onDenied);

                function onGranted()
                {
                    if (data.type == 'text') {
                        push.create(data.customerName, {
                            title: '',
                            body: data.message,
                            icon: data.image,
                            tag: 'notification' + data.uniqueId,
                            timeout: 8000,
                            onClick: function () {
                                window.focus();
                                this.close();
                            },
                        });
                    } else {
                        push.create(data.customerName, {
                            title: '',
                            body: data.filename,
                            icon: data.image,
                            tag: 'notification' + data.uniqueId,
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
            },

            /**
             * return total chat windows opened on seller panel
             */
            getTtotalChatWindows: function () {
                return totalChatWindows();
            },
            /**
             * return array of chat windows
             */
            getChatWindows: function () {
                return chatWindows();
            },
            /**
             * set total chat windows opened on seller panel
             */
            setTtotalChatWindows: function (value) {
                return totalChatWindows(value);
            },
            /**
             * set total chat windows to array
             */
            setChatWindows: function (value) {
                return chatWindows.push(value);
            },
            /**
             * remove chat window from array
             */
            remove: function (array, from, to) {
                var rest = array.slice((to || from) + 1 || array.length);
                array.length = from < 0 ? array.length + from : from;
                return array.push.apply(array, rest);
            },
            //displays the popups. Displays based on the maximum number of popups that can be displayed on the current viewport width
            displayChatWindow: function () {
                var right = 0;
                var self = this;
                var iii = 0;

                for (iii; iii < totalChatWindows(); iii++) {
                    if (chatWindows()[iii] != undefined) {
                        var element = document.getElementById(chatWindows()[iii]);
                        element.style.right = right + "px";
                        right = right + 305;
                        if (element.classList.contains('_show') == false) {
                            element.className += ' _show';
                        }
                    }
                }

                for (var jjj = iii; jjj < chatWindows().length; jjj++) {
                    var element = document.getElementById(chatWindows()[jjj]);
                    element.classList.remove("_show");
                }
            },
            //calculate the total number of popups suitable and then populate the toatal_popups variable.
            calculateChatWindows: function () {
                var self = this;
                var width = window.innerWidth;

                if (width < 350) {
                    totalChatWindows(0);
                } else {
                    totalChatWindows(parseInt(width / 320));
                }
                self.displayChatWindow();
            },

        };
    }
);