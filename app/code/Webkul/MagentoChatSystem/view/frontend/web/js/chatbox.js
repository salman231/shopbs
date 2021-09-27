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
    'mage/template',
    'uiComponent',
    'uiRegistry',
    'mage/validation',
    'ko',
    'Magento_Customer/js/customer-data',
    'Webkul_MagentoChatSystem/js/model/reply',
    'Webkul_MagentoChatSystem/js/model/manage-rating',
    'Webkul_MagentoChatSystem/js/socket.io',
    'Webkul_MagentoChatSystem/js/action/login',
    'Webkul_MagentoChatSystem/js/action/register',
    'Webkul_MagentoChatSystem/js/action/start-chat',
    'Webkul_MagentoChatSystem/js/action/assign-chat',
    'Webkul_MagentoChatSystem/js/action/update-status',
    'Webkul_MagentoChatSystem/js/action/update-profile',
    'Webkul_MagentoChatSystem/js/action/load-history',
    'Webkul_MagentoChatSystem/js/action/save-rating',
    'Webkul_MagentoChatSystem/js/action/save-report',
    'mage/apply/main',
    'Magento_Ui/js/modal/alert',
    'mage/translate'
], function (
    $,
    _,
    mageTemplate,
    Component,
    registry,
    validation,
    ko,
    customerData,
    replyModel,
    manageRating,
    io,
    loginAction,
    registerAction,
    startChatAction,
    assignChatAction,
    updateStatus,
    updateProfile,
    loadHistory,
    saveRatingAction,
    saveReportAction,
    main,
    alert
) {
    'use strict';


    var chatBox = $('[data-block=\'chatbox\']');
    /**
     * @return {Boolean}
     */
    function initSidebar()
    {
        chatBox.trigger('contentUpdated');
    }
    chatBox.on('load', function () {
        initSidebar();
    });

    var totalCustomer = ko.observableArray([]);
    var isLoading = ko.observable(false);
    var chatStatus = 0;
    if ((replyModel.receiverId() !== '' || replyModel.receiverId() !== 'undefined') &&
        window.chatboxConfig.customerData.chatStatus !== 0
    ) {
        var chatStatus = window.chatboxConfig.customerData.chatStatus;
    }
    if (window.chatboxConfig.customerData.endchat==undefined) {
        window.chatboxConfig.customerData.endchat = 0;
    }
    var canChat = ko.observable(chatStatus);
    var soundFileUrl = ko.observable(window.chatboxConfig.soundUrl);

    return Component.extend({
        defaults: {
            template: 'Webkul_MagentoChatSystem/chatbox',
            imageBoxShow: false,
            showRatingDashboard: false,
            showFeedbackBox: false,
            showReportBox: false,
            showInfoHeader: ko.observable(false),
            isLoogedIn: ko.observable(window.chatboxConfig.isCustomerLoggedIn),
            receiverIdExists: ko.observable(false),
            pageCount: 2,
            notEnableOfflineChat: window.chatboxConfig.notEnableOfflineChat,
            showReportMessage: '',
            isReportSent: false,
            showFileLoader: ko.observable(false),
            uploadPercentage: ko.observable(''),
            attachedImageData: ko.observable(null),
            fileUploadError: ko.observable(false),
            fileUploadErrorText: ko.observable(''),
            chatscreen : ko.observable(window.chatboxConfig.customerData.endchat),
            tracks: {
                imageBoxShow: true,
                showFeedbackBox: true,
                showReportBox: true,
                pageCount: true,
                showRatingDashboard: true,
                notEnableOfflineChat: true,
                showReportMessage: true,
                isReportSent: true
                // showInfoHeader: true
            }
        },
        showLoader: ko.observable(false),
        supportName: replyModel.receiverName(),
        chat: {},
        initialize: function () {
            this._super();
            let sections = ['chat-data'];
            customerData.invalidate(sections);
            customerData.reload(sections, true);
            var self = this,
                chatData = customerData.get('chat-data');
            this.update(chatData());
            this.refreshData();
            $.ajax({
                url : window.BASE_URL+"chatsystem/index/endChatStatus",
                type : 'post',
                success: function (response) {
                    if(response.endchat_status == 1) {
                        self.chatscreen(true);
                    } else {
                        self.chatscreen(false);
                    }
                }
            });
            if (window.chatboxConfig.isServerRunning == true) {
                var socket = io(window.chatboxConfig.host + ':' + window.chatboxConfig.port);
                replyModel.setSocketObject(socket);
                socket.on('adminMessage', function (data) {
                    replyModel.setResponse(data);
                });
            }
            $('.register-tab-data').hide();
            this.authenticateTmp = mageTemplate('#authentication-template');

            if (this.isChatEnable() !== 0 && this.isActive()) {
                this._startChatServer();
            }
            if (window.chatboxConfig.isServerRunning == true) {
                this._connectServer();
            };
            $('#chatbox-component').on('click', '.chathistory', function () {
                self._loadChatHistory($(this));
            });

            $('#chatbox-component').on('change', '#send-attachment', function (event) {
                event.preventDefault();
                self._sendAttachment(event);
            });
            $('body').on('click', '.chat-message-notification', function (event) {
                event.preventDefault();
                self._openChatWindowByNotify($(this));
            });
            /**
             * if agent goes offline during chat
             */
            replyModel.agentGoesOff.subscribe(function (profileData) {
                if (profileData) {
                    canChat(0);
                    setTimeout(function () {
                        replyModel.agentGoesOff(false);
                    }, 3000);
                }
            });

            replyModel.receiverId.subscribe(function (value) {
                self.receiverIdExists(true);
            });
            canChat.subscribe(function (value) {
                if (value) {
                    self._startChatServer();
                }
            });

            $('body').delegate('#chatbox-component .smiley_pad > .emoji', 'click', function (event) {
                var emoji = $(this).attr('alt');
                $(this).parents('#chat-form').children('textarea').val(function (i, text) {
                    return text + emoji;
                });
                $(this).parents('#chat-form').children('textarea').focus();
            });
            main.apply();

            replyModel.clientStatusChange(canChat());
        },
        /**
         * Refresh Data
         */
        refreshData: function () {
            var self = this,
                chatData = customerData.get('chat-data');
            this.update(chatData());
            replyModel.update(chatData());
            manageRating.update(chatData());
            if (!_.isUndefined(this.getChatParam('isCustomerLoggedIn'))) {
                this.isLoogedIn(this.getChatParam('isCustomerLoggedIn'));
            }

            if (!_.isUndefined(this.getChatParam('customerData'))) {
                canChat(this.getChatParam('customerData').chatStatus);
            }
            if (!_.isUndefined(this.getChatParam('notEnableOfflineChat'))) {
                this.notEnableOfflineChat = this.getChatParam('notEnableOfflineChat');
            }
        },
        /**
         * Get Customer Name
         */
        getCustomerName: function () {
            return replyModel.customerName();
        },
        /**
         * Get Rating Model
         */
        getRatingModel: function () {
            return manageRating;
        },
        /** Is login form enabled for current customer */
        isActive: function () {
            return this.isLoogedIn();
        },
        /**
         * Is Chat Enable
         */
        isChatEnable: function () {
            if (this.notEnableOfflineChat === true) {
                return canChat() !== 0 && this.receiverIdExists() !== 0 && replyModel.receiverId() != 0;
            } else {
                return replyModel.receiverId() != 0 || replyModel.receiverId() != false;
            }
        },
        /**
         * reply by customer to Admin
         */
        reply: function (replyForm) {
            var self = this;
            this._refreshPopup();
            if (this.isActive && window.chatboxConfig.isServerRunning == true) {
                var replyData = {},
                    formDataArray = $(replyForm).serializeArray();
                formDataArray.forEach(function (entry) {
                    var currentData = entry.value.replace(/\n/g, "<br />");
                    currentData = currentData.replace(/<script[^>]*>(?:(?!<\/script>)[^])*<\/script>/g, "");
                    replyData[entry.name] = currentData.replace(/(https?:\/\/[^\s]+)/g, function (url) {
                        return '<a href="' + url + '">' + url + '</a>';
                    });
                });

                replyData.status = canChat();
                replyData.type = 'text';

                if (!_.isNull(self.attachedImageData()) && self.attachedImageData().content) {
                    replyData.message = self.attachedImageData().content;
                    replyData.type = self.attachedImageData().message_type;
                }
                self.attachedImageData(null);
                replyModel.clientReply(replyData);
                $(replyForm).trigger("reset");
            } else {
                return true;
            }
        },
        /**
         * Reply submit by enter key press
         */
        replyByEnter: function (data, event) {
            this._refreshPopup();
            if (this.isActive && window.chatboxConfig.isServerRunning == true) {
                if (event.which == 13 && !event.shiftKey) {
                    var replyData = {};
                    if (/<(\/)?\w+/.test($(event.target).val())) {
                        alert({
                            title: $.mage.__('Warning'),
                            content: $.mage.__('HTML tags are not allowed.'),
                            actions: {
                                always: function(){
                                    $('#chat-form .type_message').focus();
                                }
                            }
                        });
                        return false;
                    }
                    var currentData = $(event.target).val().replace(/\n/g, "<br />");
                    currentData = currentData.replace(/(https?:\/\/[^\s]+)/g, function (url) {
                        return '<a href="' + url + '">' + url + '</a>';
                    });
                    currentData = currentData.replace(/<script[^>]*>(?:(?!<\/script>)[^])*<\/script>/g, "");
                    replyData.message = $(event.target).val(currentData).val();
                    replyData.status = canChat();
                    replyData.type = 'text';
                    replyModel.clientReply(replyData);

                    $(event.target).val('');
                    this.refreshData();
                    return false;
                } else if (event.shiftKey && event.keyCode == 13) {
                    return true;
                } else {
                    return true;
                }
            } else {
                return true;
            }
        },
        /**
         * Get Translation
         */
        getTranslation: function (data1, data2) {
            return $.mage.__('%1 average based on %2 ratings.').replace('%1', data1).replace('%2', data2)
        },
        /**
         * Connect Server
         */
        _connectServer: function () {
            var socket = replyModel.getSocketObject();
            socket.on('adminStatusUpdate', function (status) {
                window.chatboxConfig.isAdminLoggedIn = status;
                replyModel.adminStatus(status);
                if (status == 0) {
                    canChat(0)
                    replyModel.receiverId(0);
                    replyModel.receiverUniqueId(0);
                    replyModel.agentGoesOff(true);
                    replyModel.agentGoesOffError($.mage.__('Agent logged out, please start chat again.'));
                }
            });
        },
        /**
         * Start Chat Server
         */
        _startChatServer: function () {
            var name = window.chatboxConfig.customerData.firstname + ' ' + window.chatboxConfig.customerData.lastname;
            var statusClass = '';
            if (canChat() == 1) {
                statusClass = 'active';
            } else if (canChat() == 2) {
                statusClass = 'busy';
            } else if (canChat() == 0) {
                statusClass = 'offline';
            }
            var clientData = {
                sender: 'customer',
                name: replyModel.customerName(),
                email: window.chatboxConfig.customerData.email,
                customerId: replyModel.customerId(),
                customer_unique_id: replyModel.customerUniqueId(),
                receiver: replyModel.receiverId(),
                chat_status: canChat(),
                status: canChat(),
                class: statusClass,
                image: this.getProfileImage()
            }
            if (window.chatboxConfig.isServerRunning == true) {
                var socket = replyModel.getSocketObject();
                socket.emit('newUserConneted', clientData);
                socket.on('adminStatusUpdate', function (status) {
                    replyModel.adminStatus(status);
                    if (replyModel.adminStatus() == 0) {
                        canChat(0)
                        replyModel.receiverId(0);
                        replyModel.receiverUniqueId(0);
                        replyModel.agentGoesOff(true);
                        replyModel.agentGoesOffError($.mage.__('Agent logged out, please start chat again.'));
                    }
                });
            }
            replyModel.callEmojify('chatbox-component');
        },
        /**
         * Add Attachment
         */
        addAttachment: function (model, e) {
            $(e.currentTarget).parents('form').find('.msg-attachment').trigger('click');
        },
        /**
         * Select File
         */
        selectFile: function ($model, e) {
            var self = $model;
            self.refreshData();
            e.stopImmediatePropagation();
            var currentElement = $(e.currentTarget),
                fileType = e.originalEvent.target.files[0].type,
                data = {};
            var restrictedFiles = ["php", "exe", "js"],
                error = false;
            if (restrictedFiles.indexOf(e.originalEvent.target.files[0].name.split('.').pop()) > -1) {
                error = true;
                self.fileUploadError(true);
                self.fileUploadErrorText($.mage.__('File type not supported'));
                self.removeErrorMessage();
            }
            var element = $(e.currentTarget).attr('data-form');

            if (!error) {
                if (fileType.indexOf("image") >= 0) {
                    var type = 'image';
                } else {
                    var type = 'file';
                }
                self.siofu = replyModel.getSocketFileUpload();

                // Do something when a file is uploaded:
                self.siofu.addEventListener("complete", function (event) {
                    self.showFileLoader(false);
                    var replyData = {};
                    replyData.message_type = type;
                    replyData.content = event.detail.savedFileName;
                    self.attachedImageData(replyData);
                    $(e.target.form).submit();
                });

                self.siofu.addEventListener("start", function (event) {

                });
                // Do something on upload progress:
                self.siofu.addEventListener("progress", function (event) {
                    self.showFileLoader(true);
                    var percent = event.bytesLoaded / event.file.size * 100;
                    self.uploadPercentage("File is " + percent.toFixed(2) + "% percent loaded");
                });

                self.siofu.addEventListener("error", function (data) {
                    if (data.code === 1) {
                        self.fileUploadError(true);
                        self.fileUploadErrorText($.mage.__('Maximum allowe size is ' + window.chatboxConfig.maxFileSize + 'MB'));
                    }
                    self.showFileLoader(false);
                    self.removeErrorMessage();
                });
                self.siofu.resetFileInputs = true
                self.siofu.maxFileSize = parseInt(window.chatboxConfig.maxFileSize) * 1024 * 1024;
                self.siofu.listenOnInput(e.currentTarget);
                e.currentTarget.removeEventListener("change", self.siofu.prompt, false);
                self.siofu = null;
            }
        },
        /**
         * Remove Error Message
         */
        removeErrorMessage: function () {
            var self = this;
            if (self.fileUploadError()) {
                setTimeout(function () {
                    self.fileUploadError(false);
                }, 5000);
            }
        },
        /**
         * Send Attachment
         */
        _sendAttachment: function (e) {
            var fileType = e.originalEvent.target.files[0].type;
            if (fileType.indexOf("image") >= 0) {
                var type = 'image';
            } else {
                var type = 'file';
            }
            var replyData = {};
            var file = e.originalEvent.target.files[0];
            replyData.type = type;
            replyData.status = canChat();
            var reader = new FileReader();
            reader.onload = function (evt) {
                replyData.message = evt.target.result;
                replyModel.clientReply(replyData);
            }
            reader.readAsDataURL(file);
        },
        /**
         * Open Chat Window
         */
        _openChatWindow: function (element, event) {
            if ($(event.currentTarget).hasClass('windowOpen')) {
                $(event.currentTarget).removeClass('windowOpen');
                $(event.currentTarget).parents('#chatbox-component').children('.chat').hide();
                $(event.currentTarget).children('#maxi-chat').show();
                $(event.currentTarget).children('#minim-chat').hide();
            } else {
                $(event.currentTarget).addClass('windowOpen');
                $(event.currentTarget).parents('#chatbox-component').children('.chat').show();
                $(event.currentTarget).children('#maxi-chat').hide();
                $(event.currentTarget).children('#minim-chat').show();
                $('.chat-history').trigger('scrollChat');
                this._refreshPopup();
            }
            replyModel.callEmojify('chatbox-component');
        },
        /**
         * Open Chat Window By Notify
         */
        _openChatWindowByNotify: function (element) {
            if (element.length) {
                $('body').find('#chatbox-component').children('.chat').show();
                $('body').find('#chatbox-component').children('.clearfix.customer-controls').show();

                $(element).hide();
                $('#minim-chat').show();
                this._refreshPopup();
            }
            replyModel.callEmojify('chatbox-component');
        },
        /**
         * Minimize Chat Window
         */
        _minimizeChatWindow: function (element) {
            if (element.length) {
                $(element).parents('#chatbox-component').children('.chat').hide();
                $(element).parents("#chatbox-component").children('.clearfix.customer-controls').hide();
                $(element).hide();
                $('#maxi-chat').show();
                this._refreshPopup();
            }
        },
        /**
         * Scrolled 
         */
        scrolled: function (data, event) {
            var elem = event.target;
            if (elem.scrollTop == 0) {
                $(event.currentTarget).animate({ scrollTop: event.target.scrollHeight });
            }
        },
        /**
         * Get Send Image
         */
        getSendImage: function () {
            return window.chatboxConfig.sendImage;
        },

        /** Provide login action */
        login: function (loginForm) {
            var self = this;
            var loginData = {},
                formDataArray = $(loginForm).serializeArray();

            formDataArray.forEach(function (entry) {
                loginData[entry.name] = entry.value;
            });
            loginData.dateTime = replyModel.getDate() + replyModel.getTime();
            loginData.customer_id = window.chatboxConfig.customerData.id;
            loginData.startChat = true;
            loginData.agent_id = 0;
            loginData.agent_unique_id = 0;
            if ($(loginForm).validation() &&
                $(loginForm).validation('isValid')
            ) {
                loginAction(loginData, false, undefined, canChat, self.showLoader, self.isLoogedIn);
            }
            replyModel.callEmojify('chatbox-component');
            this.refreshData();
        },
        /**
         * register new customer from chat window
         */
        register: function (registerForm) {
            var self = this;
            var registerData = {},
                formDataArray = $(registerForm).serializeArray();

            formDataArray.forEach(function (entry) {
                registerData[entry.name] = entry.value;
            });

            registerData.dateTime = replyModel.getDate() + replyModel.getTime();
            registerData.startChat = true;

            if ($(registerForm).validation() &&
                $(registerForm).validation('isValid')
            ) {
                registerAction(registerData, false, undefined, self.showLoader, canChat, self.isLoogedIn).always(function () {
                    self.showLoader(false);
                });
            }
            replyModel.callEmojify('chatbox-component');
            this.refreshData();
        },
        /**
         * Start Chat
         */
        startChat: function (startChatForm) {
            var self = this;
            self.chatscreen(true);
            var chatData = {},
                formDataArray = $(startChatForm).serializeArray();
            formDataArray.forEach(function (entry) {
                chatData[entry.name] = entry.value;
            });
            chatData.dateTime = replyModel.getDate() + replyModel.getTime();
            chatData.startChat = false;
            chatData.type = 'text';
            chatData.customer_id = replyModel.customerId();
            chatData.unique_id = replyModel.customerUniqueId();
            chatData.status = 1;

            if ($(startChatForm).validation() &&
                $(startChatForm).validation('isValid')
            ) {
                this._connectServer();
                if (replyModel.customerUniqueId() == null) {
                    chatData.agent_id = 0;
                    chatData.agent_unique_id = 0;
                    self.showLoader(true);
                    startChatAction(chatData, canChat).always(function (data) {
                        var data = $.parseJSON(data);
                        chatData.unique_id = data.unique_id;
                        replyModel.customerUniqueId(data.unique_id);
                        replyModel.customerId(data.customer_id);
                        assignChatAction(chatData, canChat).then(function () {
                            self.showLoader(false);
                            if (canChat()) {
                                self._startChatServer();
                                replyModel.clientReply(chatData);
                                replyModel.clientStatusChange(1);
                                $('body').find('#chatbox-component').children('.clearfix.customer-controls').show();
                            }
                        });
                    });
                } else {
                    self.showLoader(true);
                    assignChatAction(chatData, canChat).then(function () {
                        self.showLoader(false);
                        if (canChat()) {
                            self._startChatServer();
                            replyModel.clientReply(chatData);
                            replyModel.clientStatusChange(1);
                            $('body').find('#chatbox-component').children('.clearfix.customer-controls').show();
                        }
                    });
                }
            }
            this.refreshData();
        },
        /**
         * Load Chat History
         */
        _loadChatHistory: function (page) {
            var self = this;
            var loadData = {};
            if (page !== 'undefined') {
                loadData['currentPage'] = page;
                loadData['customerId'] = window.chatboxConfig.customerData.id;
                loadData['uniqueId'] = window.chatboxConfig.customerData.uniqueId;
                self.showLoader(true);
                loadHistory(loadData).always(function () {
                    self.showLoader(false);
                    self.pageCount = self.pageCount + 1;
                });
            }
            replyModel.callEmojify('chatbox-component');
            this.refreshData();
        },
        /**
         * submit rating for agent
         */
        submitRating: function (ratingForm) {
            var self = this;
            if (this.isActive && $(ratingForm).valid()) {
                var ratingData = {},
                    formDataArray = $(ratingForm).serializeArray();
                formDataArray.forEach(function (entry) {
                    var currentData = entry.value.replace(/\n/g, "<br />");
                    ratingData[entry.name] = currentData.replace(/(https?:\/\/[^\s]+)/g, function (url) {
                        return '<a href="' + url + '">' + url + '</a>';
                    });
                });
                self.showLoader(true);
                saveRatingAction(ratingData).fail(
                    function (response) {
                        self.showLoader(false);
                    }
                ).done(
                    function (response) {
                        self.showLoader(false);
                        self.showRatingDashboard = false;
                        self.isReportSent = true;
                        self.showReportMessage = $.mage.__('Thanks for you valuable feedback.');
                        ratingForm.reset();
                    }
                );
            }
            this.refreshData();
        },
        /**
         * Show Rating Board
         */
        showRatingBoard: function () {
            if (this.isActive()) {
                if (!$('.windowOpen').length) {
                    $('.chatbox-header').trigger('click');
                }
                this.showInfoHeader(false);
                this.showRatingDashboard = this.showRatingDashboard === true ? false : true;
                this.showReportBox = false;
                this.imageBoxShow = false;
            }
        },
        /**
         * Show Rating Box
         */
        showRatingBox: function () {
            this.showInfoHeader(false);
            this._refreshPopup();
            this.showFeedbackBox = true;
            this.showReportBox = false;
            main.apply();
        },
        /**
         * Close Rating Box
         */
        closeRatingBox: function () {
            this.showFeedbackBox = false;
            this.showReportMessage = '';
        },


        /**
         * Manage Report Section
         */
        showReportModel: function () {
            if (!$('.windowOpen').length) {
                $('.chatbox-header').trigger('click');
            }
            this.showInfoHeader(false);
            this._refreshPopup();
            this.showReportBox = true;
            this.showFeedbackBox = false;
            this.showRatingDashboard = false;
            main.apply();
        },
        /**
         * Close Report Box
         */
        closeReportBox: function () {
            this.showReportBox = false;
            this.isReportSent = false;
            this.showReportMessage = '';
            // this.isReportSent = true;
        },

        /**
         * submit rating for agent
         */
        submitReport: function (reportForm) {
            var self = this;
            $(reportForm).mage('validation', {});
            if (this.isActive && $(reportForm).valid()) {
                var reportData = {},
                    formDataArray = $(reportForm).serializeArray();
                formDataArray.forEach(function (entry) {
                    var currentData = entry.value.replace(/\n/g, "<br />");
                    reportData[entry.name] = currentData.replace(/(https?:\/\/[^\s]+)/g, function (url) {
                        return '<a href="' + url + '">' + url + '</a>';
                    });
                });
                self.showLoader(true);
                saveReportAction(reportData).fail(
                    function (response) {
                        self.showLoader(false);
                    }
                ).done(
                    function (response) {
                        self.showLoader(false);
                        self.isReportSent = true;
                        self.showReportMessage = $.mage.__('Report submitted successfuly.');
                        reportForm.reset();
                    }
                );
            }
            this.refreshData();
        },

        /**
         * update user chat status
         */
        _updateChatStatus: function (uiModel, element) {
            let self = uiModel;
            let status = $(element.currentTarget).attr('id');
            let chatData = {};
            let endchat = false;
            if( typeof($(element.currentTarget).data('endchat')) === "undefined" ){
                endchat = false;
            } else {
                endchat = true;
            }
            if (status !== 'undefined') {
                chatData['status'] = status;
                self.showLoader(true);
                updateStatus(chatData, canChat, self.chatscreen, endchat).always(function () {
                    replyModel.clientStatusChange(status);
                    if(endchat) {
                        replyModel.receiverId(false);
                        $.ajax({
                            url : window.BASE_URL+"chatsystem/index/endChatStatus",
                            data : {'status':0},
                            type : 'post',
                            success: function (response) {
                                if(response.endchat_status == 1) {
                                    self.chatscreen(true);
                                } else {
                                    self.chatscreen(false);
                                }
                            }
                        });
                    }
                    self.showLoader(false);
                    self.refreshData();
                });
            }
        },

        /**
         * show profile image box
         */
        _updateProfile: function () {
            this._refreshPopup();
            this.showInfoHeader(false);
            this.imageBoxShow = true;
        },

        /**
         * Update chatbox content.
         *
         * @param {Object} updatedChat
         * @returns void
         */
        update: function (updatedChat) {
            _.each(updatedChat, function (value, key) {
                if (!this.chat.hasOwnProperty(key)) {
                    this.chat[key] = ko.observable();
                }
                this.chat[key](value);
            }, this);
        },

        /**
         * Get chatbox param by name.
         * @param {String} name
         * @returns {*}
         */
        getChatParam: function (name) {
            if (!_.isUndefined(name)) {
                if (!this.chat.hasOwnProperty(name)) {
                    this.chat[name] = ko.observable();
                }
            }

            return this.chat[name]();
        },

        /**
         * upload profile image
         */
        uploadProfileImage: function (image) {
            var data = new FormData();
            data.append('file', $('#profile_image')[0].files[0]);
            updateProfile(data, $('.profile-setting-box'), this.showLoader);
            this._refreshPopup();
        },
        /**
         * Show Selected Image
         */
        showSelectedImage: function () {
            var oFReader = new FileReader();
            oFReader.readAsDataURL(document.getElementById("profile_image").files[0]);

            oFReader.onload = function (oFREvent) {
                document.getElementById("user-profile-image").src = oFREvent.target.result;
            };
        },
        /**
         * Register Tab Window
         */
        _registerTabWindow: function (element) {
            $('.login-tab-data').hide();
            $('.register-tab-data').show();
        },
        /**
         * Login Tab Window
         */
        _loginTabWindow: function (element) {
            $('.login-tab-data').show();
            $('.register-tab-data').hide();
        },
        /**
         * Refresh Popup
         */
        _refreshPopup: function () {
            $('.list-group').each(function () {
                $(this).hide();
            });
            this.showReportBox = false;
            this.showFeedbackBox = false;
            this.showRatingDashboard = false;
            this.imageBoxShow = false;
        },
        /**
         * Cancel Profile Popup
         */
        _cancelProfilePopup: function () {
            this._refreshPopup();
        },
        /**
         * Hide User Info
         */
        hideUserInfo: function (data) {
            var model = registry.get('chatbox-content');
            model.showInfoHeader(false);
            model._refreshPopup();
        },
        /**
         * Hide Emoji Box
         */
        hideEmojiBox: function () {
            $('#chat-form').children('.dropup').removeClass('open');
        },
        /**
         * Enable Disable Sound
         */
        _enableDisableSound: function (uiModel, element) {
            if ($(element.currentTarget).hasClass('disable')) {
                $(element.currentTarget).css("background-position", "-21px 0px");
                $(element.currentTarget).addClass('enable');
                $(element.currentTarget).removeClass('disable');
                soundFileUrl(window.chatboxConfig.soundUrl);
            } else {
                $(element.currentTarget).css("background-position", "0px 0px");
                $(element.currentTarget).addClass('disable');
                $(element.currentTarget).removeClass('enable');
                soundFileUrl('');
            }
        },
        /**
         * Is History Avialable
         */
        isHistoryAvialable: function () {
            return replyModel.chatHistory().length !== 0;
        },
        /**
         * Get Chat History
         */
        getChatHistory: function () {
            setTimeout(function(){
                replyModel.callEmojify('chatbox-component');
            },500)
            return replyModel.chatHistory();
        },
        /**
         * Show Status
         */
        showStatus: function () {
            $('.wk_chat_status_options').slideToggle('fast');
            $('.profile-setting-box').hide();
            $('.wk_chat_history_options').hide();
            $('.wk_chat_setting_options').hide();
        },
        /**
         * Show Load Message Panel
         */
        showLoadMessagePanel: function () {
            $('.wk_chat_history_options').slideToggle('fast');
            $('.profile-setting-box').hide();
            $('.wk_chat_status_options').hide();
            $('.wk_chat_setting_options').hide();
        },
        /**
         * Show Setting Panel
         */
        showSettingPanel: function () {
            $('.wk_chat_history_options').hide();
            $('.wk_chat_status_options').hide();
            $('.profile-setting-box').hide();
            $('.wk_chat_setting_options').slideToggle('fast');
        },
        /**
         * Show Information
         */
        showInformation: function () {
            if (!$('.windowOpen').length) {
                $('.chatbox-header').trigger('click');
            }
            this._refreshPopup();
            this.showInfoHeader() == true ? this.showInfoHeader(false) : this.showInfoHeader(true);
        },
        /**
         * Image Path
         */
        imagePath: function () {
            return window.chatboxConfig.opeChatImage;
        },
        /**
         * Support Status
         */
        supportStatus: function () {
            return replyModel.adminStatus();
        },
        /**
         * Get Customer Status
         */
        getCustomerStatus: function () {
            if (canChat() == 1) {
                return '#1a8a34';
            } else if (canChat() == 2) {
                return '#D10000';
            } else {
                return '#77777A';
            }
        },
        /**
         * Get Admin Status
         */
        getAdminStatus: function () {
            // replyModel.adminStatus(window.chatboxConfig.isAdminLoggedIn);
            if (replyModel.adminStatus() == 1) {
                return '#1a8a34';
            } else if (replyModel.adminStatus() == 2) {
                return '#D10000';
            } else {
                return '#77777A';
            }
        },
        /**
         * Get Profile Image
         */
        getProfileImage: function () {
            return replyModel.profileImageUrl();
        },
        /**
         * Get Download Image
         */
        getDownloadImage: function () {
            return window.chatboxConfig.downloadImage;
        },
        /**
         * Get Loading State
         */
        getLoadingState: function () {
            return replyModel.loadingState();
        },
        /**
         * Get Loader Image
         */
        getLoaderImage: function () {
            return window.chatboxConfig.loaderImage;
        },
        /**
         * Get Attachment Image
         */
        getAttachmentImage: function () {
            return window.chatboxConfig.attachmentImage;
        },
        /**
         * Get Admin Image
         */
        getAdminImage: function () {
            if (!_.isUndefined(this.getChatParam('adminImage'))) {
                return this.getChatParam('adminImage');
            }
            return window.chatboxConfig.adminImage;
        },
        /**
         * Get Sound Url
         */
        getSoundUrl: function () {
            return soundFileUrl();
        },
        /**
         * Get Support Name
         */
        getSupportName: function () {
            this.refreshData();
            if (_.isUndefined(replyModel.receiverName()) || _.isNull(replyModel.receiverName())) {
                if (!_.isUndefined(this.getChatParam('adminChatName'))) {
                    return this.getChatParam('adminChatName');
                }
                return window.chatboxConfig.adminChatName;
            } else {
                return replyModel.receiverName();
            }
        },
        /**
         * Is Agent Off
         */
        isAgentOff: function () {
            return replyModel.agentGoesOff();
        },
        /**
         * Get Agent Error
         */
        getAgentError: function () {
            return replyModel.agentGoesOffError();
        },
        /**
         * Open Emoji Box
         */
        openEmojiBox: function (data, event) {
            if ($(event.currentTarget).hasClass('open')) {
                $(event.currentTarget).removeClass('open');
            } else {
                $(event.currentTarget).addClass('open');
            }
        },


    });
});