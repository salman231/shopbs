/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MagentoChatSystem
 * @author    Webkul
 * @copyright Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
define([
    'jquery',
    'underscore',
    'uiComponent',
    'mage/template',
    'ko',
    'Webkul_MagentoChatSystem/js/modal/reply',
    'Webkul_MagentoChatSystem/js/chatbox',
    'Webkul_MagentoChatSystem/js/socket.io',
    'Webkul_MagentoChatSystem/js/action/load-history',
    'Webkul_MagentoChatSystem/js/action/clear-history',
    'Webkul_MagentoChatSystem/js/action/update-status',
    'Magento_Ui/js/modal/alert',
    'Magento_Ui/js/modal/confirm',
    'mage/apply/main',
    'mage/translate'
], function (
    $,
    _,
    Component,
    mageTemplate,
    ko,
    replyModel,
    chatbox,
    io,
    loadHistoryAction,
    clearHistoryAction,
    updateStatus,
    alert,
    confirm,
    main
) {
    'use strict';
    var totalCustomer = ko.observableArray([]);
    var isLoading = ko.observable(false);
    var enableUsers = window.enableChatUsersConfig.enableUserData;
    return Component.extend({
        defaults: {
            template: 'Webkul_MagentoChatSystem/active-model'
        },
        conditionModel: '',
        userTyping: ko.observable(),
        supportName: window.chatboxConfig.adminChatName,
        usersList: ko.observableArray(),
        enabledCustomerList: ko.observableArray([]),
        tempEnabledCustomerList: ko.observableArray([]),
        searchQuery: ko.observable(''),
        totalChatWindows: replyModel.getTtotalChatWindows(),
        chatWindows: replyModel.getChatWindows(),
        options: {
            openWindowCount: 0
        },
        initialize: function () {
            var self = this,
                socket = replyModel.getSocketObject();
            this._super();
            this.chatTmpl = mageTemplate('#chat_window_template');

            $('#chatbox-active-users').on('click', '.chatStatus', function (event) {
                self._updateChatStatus($(this));
            });

            window.addEventListener("load", replyModel.calculateChatWindows(this));
            $(window).resize(function () {
                replyModel.calculateChatWindows(this)
            });

            replyModel.refreshChatModel.subscribe(function (data) {
                if (!_.contains(_.pluck(self.enabledCustomerList(), 'uniqueId'), data.uniqueId)) {
                    self.enabledCustomerList().splice(0, 0, data);
                    self.tempEnabledCustomerList(self.enabledCustomerList().slice());
                }
                self.enabledCustomerList().forEach(element => {
                    if(element.uniqueId === data.uniqueId) {
                        element.chat_status = parseInt(data.chat_status);
                        element.class = "active";
                    }
                });

            });

            replyModel.getResponse().subscribe(function (response) {
                if ($('#chatbox-component').find('#live-chat-' + response.customerId).length == 0) {
                    $('#user_' + response.customerId).addClass('msg-notify');
                } else if ($('#chatbox-component').find('#live-chat-' + response.customerId).hasClass('_show') == false) {
                    $('#user_' + response.customerId).addClass('msg-notify');
                }
            });

            replyModel.customerStatus.subscribe(function (status) {
                let tempStatus = status[0];
                self.enabledCustomerList().forEach(element => {
                    if(element.customerId == tempStatus.customerId) {
                        element.chat_status = parseInt(tempStatus.status);
                        element.class = self.customerChatStatus(parseInt(tempStatus.status));
                    }
                });
            });  

            /**
             * assign chat customer on page refresh for admin.
             */

            if (_.isEmpty(this.enabledCustomerList())) {
                _.each(enableUsers, function (data) {
                    self.enabledCustomerList.push(data);
                });
            }
            this.tempEnabledCustomerList(this.enabledCustomerList.slice());

            this.enabledCustomerList = ko.pureComputed(function () {
                var query = self.searchQuery();
                if (query) {
                    return self.tempEnabledCustomerList().filter(function (i) {
                        if (i.customerName.toLowerCase().indexOf(query) >= 0) {
                            return i.customerName.toLowerCase().indexOf(query) >= 0;
                        } else {
                            return i.email.toLowerCase().indexOf(query) >= 0;
                        }
                    });
                } else {
                    return self.tempEnabledCustomerList();
                }
            });
            main.apply();       
        },
        /**
         * Open Model
         */
        _openModel: function (pannel) {
            main.apply();
            if ($(pannel).hasClass('close')) {
                $('.active-users-model').css('right', '0');
                $(pannel).removeClass('close');
                $(pannel).addClass('open');
            } else {
                $('.active-users-model').css('right', '-250px');
                $(pannel).removeClass('open');
                $(pannel).addClass('close');
            }

        },

        /**
         * open right chat panel
         */
        openChatPanel: function (data, el) {
            if ($(el.target).hasClass('open')) {
                this.closeChatPanel();
            } else {
                $('.active-users-model').css('right', '0');
                $(el.target).addClass('open');
            }
        },

        /**
         * hide right chat panel
         */
        closeChatPanel: function () {
            $('.chat__menu').removeClass('_show');
            $('.active-users-model').css('right', '-250px');
            $('.pannel-control').removeClass('open');
            $('.pannel-control').addClass('close');
        },
        /**
         * Set User List
         */
        setUserList: function () {
            var self = this;
            replyModel.usersList.push();
            $.each(enableUsers, function (i, v) {
                replyModel.usersList.push(v);
            });
        },
        /**
         * Get User List
         */
        getUserList: function () {
            return replyModel.usersList();
        },
        /**
         * Refresh Chat User List
         */
        refreshChatUserList: function () {
        },
        /**
         * Go Offline
         */
        goOffline: function () {
            console.log('not implemented yet!');
        },
        /**
         * Show Status
         */
        showStatus: function () {
            $('.wk_chat_status_options').slideToggle('fast');
        },
        /**
         * Support Status
         */
        supportStatus: function () {
            return true;
        },
        /**
         * Update Chat Status
         */
        _updateChatStatus: function (element) {
            var self = this;
            var status = $(element).attr('id');
            var statusData = {};
            if (status !== 'undefined') {
                statusData['status'] = status;
                updateStatus(statusData).always(function () {
                    replyModel.adminStatusChange(status);
                    if (status == 0) {
                        location.reload();
                    }
                });
            }
        },
        /**
         * Support Status
         */
        supportStatus: function () {
            if (replyModel.adminStatusChange() == 1) {
                return '#1a8a34';
            } else if (replyModel.adminStatusChange() == 2) {
                return '#D10000';
            } else {
                return '#77777A';
            }
        },
        /**
         * Customer Chat Status
         */
        customerChatStatus: function (status) {
            if (status == 1) {
                return 'active';
            } else if (status == 2) {
                return 'busy';
            } else {
                return 'offline';
            }
        },
        /**
         * Open Chat Window
         */
        openChatWindow: function (selectedUser) {
            var chatTmplate = mageTemplate('#chat_window_template');
            var id = "live-chat-" + selectedUser.customerId;
            $('#user_' + selectedUser.customerId).removeClass('msg-notify');
            for (var iii = 0; iii < replyModel.getChatWindows().length; iii++) {
                //already registered. Bring it to front.
                if (id == replyModel.getChatWindows()[iii]) {
                    replyModel.remove(replyModel.getChatWindows(), iii);
                    replyModel.getChatWindows().unshift(id);
                    replyModel.calculateChatWindows();
                    return;
                }
            }
            if ($('#chatbox-component').find('#live-chat-' + selectedUser.customerId).length === 0) {
                var data = selectedUser,
                    chatTmpl;
                if (selectedUser !== 'undefined') {
                    chatTmpl = chatTmplate({
                        data: selectedUser
                    });
                    $(chatTmpl)
                        .appendTo($('#chatbox-component'));

                    ko.applyBindings(
                        chatbox(
                            selectedUser.customerId,
                            'upload' + selectedUser.customerId
                        ),
                        document.getElementById("live-chat-" + selectedUser.customerId)
                    );
                    replyModel.getChatWindows().unshift("live-chat-" + selectedUser.customerId);
                    replyModel.calculateChatWindows();
                }
                var loadData = {};
                loadData['loadtime'] = 0;
                loadData['customerId'] = selectedUser.customerId;
                loadData['uniqueId'] = selectedUser.uniqueId;
                loadHistoryAction(loadData).always(function () {});

                replyModel.openWindowCount(replyModel.openWindowCount() + 1);
                replyModel.callEmojify('chatbox-component');
            } else {
                replyModel.getChatWindows().unshift("live-chat-" + selectedUser.customerId);
                replyModel.calculateChatWindows();
            }
        },
        /**
         * Clear Chat History
         */
        clearChatHistory: function (selectedUser) {
            confirm({
                title: $.mage.__('Attension!'),
                content: $.mage.__('Are you sure want to clear chat history.'),
                actions: {
                    confirm: function (data) {
                        clearHistoryAction(selectedUser).always(function () {});
                    }
                }
            });
        }

    });
});