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
    'underscore',
    'uiComponent',
    'mage/validation',
    'ko',
    'Webkul_MagentoChatSystem/js/modal/reply',
    'Webkul_MagentoChatSystem/js/action/load-history',
    'Webkul_MagentoChatSystem/js/socket.io',
    'Webkul_MagentoChatSystem/js/manage-cookies',
    'Magento_Ui/js/modal/alert'
], function (
    $,
    _,
    Component,
    validation,
    ko,
    replyModel,
    loadHistory,
    io,
    cookies,
    alert
) {
    'use strict';
    var totalCustomer = ko.observableArray([]);
    var isLoading = ko.observable(false);
    return Component.extend({
        defaults: {
            template: 'Webkul_MagentoChatSystem/chatbox'
        },
        userTyping: ko.observable(),
        showLoader: ko.observable(false),
        attachedImageData: ko.observable(null),
        fileUploadErrorText: ko.observable(''),
        initialize: function (chatWindow, uploadObserverName) {
            this._super();

            if (window.chatboxConfig.isServerRunning == 1) {
                this._connectServer();
            }
            replyModel.callEmojify('chatbox-component');
            var self = this;
            self.showFileLoader = this[chatWindow] = ko.observable(false);
            self.uploadPercentage = this[uploadObserverName] = ko.observable();
            self.fileUploadError = this['fileError_' + chatWindow] = ko.observable(false);
            $('#chatbox-component').on('click', '.load_history', function (event) {
                self.showLoadMessagePanel($(this));
            });
            $('#chatbox-component').on('click', '.action-reply', function (event) {
                event.preventDefault();
                self.reply('#form-' + $(this).attr('id'));
            });
            $('#chatbox-component').on('click', '.chathistory', function (event) {
                event.preventDefault();
                self._loadChatHistory($(this));
            });
            $('#chatbox-component').on('change', '[id^="send-attachment-"]', function (event) {
                event.preventDefault();
                self._sendAttachment(event);
            });
            $('body').on('click', '.msg_notification', function (event) {
                event.preventDefault();
                self._openChatWindowByNotify($(this));
            });
            $('body').on('keypress', '.type_message', function (event) {
                if (event.which == 13 && !event.shiftKey) {
                    $(this).parent('form').find('.action').click();
                }
            });
            if (!_.isObject(chatWindow)) {
                $('body').delegate('#form-reply-' + chatWindow + ' .smiley_pad > .emoji', 'click', function (event) {

                    var emoji = $(this).attr('alt');
                    $(this).parents('.chat-form').children('textarea').val(function (i, text) {
                        return text + emoji;
                    });
                    $(this).parents('.chat-form').children('textarea').focus();
                });
            }
        },

        /**
         * Reply submit by enter key press
         */
        replyByEnter: function (data, event) {
            if (event.which == 13 && !event.shiftKey) {
                var replyData = {};
                if (/<(\/)?\w+/.test($(event.target).val())) {
                    alert({
                        title: $.mage.__('Warning'),
                        content: $.mage.__('HTML tags are not allowed.'),
                        actions: {
                            always: function(){
                                $(event.target).focus();
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
                replyData.reply = $(event.target).val(currentData).val();
                replyData.type = 'text';
                replyData.customerId = $(event.target).next('input').val();

                replyModel.adminReply(replyData);
                $(event.target).val('');
                return false;
            } else if (event.shiftKey && event.keyCode == 13) {
                return true;
            } else {
                return true;
            }
            /**/
        },
        /**
         * Reply
         */
        reply: function (replyForm) {
            var replyData = {},
                formDataArray = $(replyForm).serializeArray(),
                self = this;

            formDataArray.forEach(function (entry) {
                var currentData = entry.value.replace(/\n/g, "<br />");
                currentData = currentData.replace(/<script[^>]*>(?:(?!<\/script>)[^])*<\/script>/g, "");
                replyData[entry.name] = currentData.replace(/(https?:\/\/[^\s]+)/g, function (url) {
                    return '<a href="' + url + '">' + url + '</a>';
                });
            });
            replyData.type = 'text';
            if (!_.isNull(self.attachedImageData()) && self.attachedImageData().content) {
                replyData.reply = self.attachedImageData().content;
                replyData.type = self.attachedImageData().message_type;
            }
            self.attachedImageData(null);
            replyModel.adminReply(replyData);
            $(replyForm).trigger("reset");
        },
        /**
         * Connect Server
         */
        _connectServer: function () {
            var name = window.chatboxConfig.adminData.name;
            var clientData = {
                sender: 'admin',
                customerName: name,
                adminId: window.chatboxConfig.adminData.id,
            }
            var socket = io(window.chatboxConfig.host + ':' + window.chatboxConfig.port);
            replyModel.setSocketObject(socket);
            socket.on('connect', function () {
                socket.emit('newUserConneted', clientData);
            });
            socket.on('customerMessage', function (data) {
                replyModel.setResponse(data);
                replyModel.setRefreshList(data);
            });
            socket.on('customerStatusChange', function (data) {
                var statusData = [data];
                replyModel.setCustomerStatus(statusData);
            });

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
            e.stopImmediatePropagation();
            var self = $model,
                currentElement = $(e.currentTarget),
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
                    self.uploadPercentage(percent.toFixed(2) + $.mage.__("% percent loaded"));
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
            var customerId = e.target.id.split('-')[2];
            var fileType = e.originalEvent.target.files[0].type;
            if (fileType.indexOf("image") >= 0) {
                var type = 'image';
            } else {
                var type = 'file';
            }
            var replyData = {};
            var socket = replyModel.getSocketObject();
            var file = e.originalEvent.target.files[0];
            replyData.type = type;
            replyData.customerId = customerId;
            var reader = new FileReader();
            reader.onload = function (evt) {
                replyData.reply = evt.target.result;
                replyModel.adminReply(replyData);
            }
            reader.readAsDataURL(file);
        },
        /**
         * Open Chat Window
         */
        _openChatWindow: function (data, el) {
            if ($(el.currentTarget).hasClass('wkopen')) {
                $(el.currentTarget).removeClass('wkopen');
                $(el.currentTarget).addClass('wkclose');
                $(el.currentTarget).find('#maxi-chat').show();
                $(el.currentTarget).find('#minim-chat').hide();
                $(el.currentTarget).parents('.chat-window').css('margin', '0 0 -360px 0');
            } else {
                $(el.currentTarget).addClass('wkopen');
                $(el.currentTarget).removeClass('wkclose');
                $(el.currentTarget).find('#maxi-chat').hide();
                $(el.currentTarget).find('#minim-chat').show();
                $(el.currentTarget).parents('.chat-window').css('margin', 0);
            }
            replyModel.callEmojify('chatbox-component');
        },
        /**
         * Open Chat Window By Notify
         */
        _openChatWindowByNotify: function (element) {
            if (element.length) {
                var id = $(element).attr('id');
                if ($('body').find('#live-chat-' + id).length) {
                    $('body').find('#live-chat-' + id).css('margin', 0);
                    $('body').find('#live-chat-' + id).children('#minim-chat').show();
                }
            }
        },
        /**
         * Minimize Chat Window
         */
        _minimizeChatWindow: function (element) {
            if (element.length) {
                $(element).parents('[id^="live-chat-"]').css('margin', '0 0 -360px 0');
                $(element).parents('[id^="live-chat-"]').find('header').addClass('wkclose');
                $(element).parents('[id^="live-chat-"]').find('header').removeClass('wkopen');
                $(element).hide();
                $(element).siblings('#maxi-chat').show();
            }
        },
        /**
         * Show Load Message Panel
         */
        showLoadMessagePanel: function (element) {
            $(element).children('.wk_chat_history_options').slideToggle('fast');
        },
        /**
         * Load Chat History
         */
        _loadChatHistory: function (element) {
            var self = this;
            var loadtime = $(element).attr('id');
            var result = loadtime.split('_customer_');
            var loadData = {};
            if (loadtime !== 'undefined') {
                loadData['loadtime'] = result[0];
                loadData['customerId'] = result[1];
                loadData['uniqueId'] = result[2];
                loadHistory(loadData).always(function () {
                });
            }
            replyModel.callEmojify('chatbox-component');
        },
        /**
         * Close Chat Window
         */
        closeChatWindow: function (data, el) {
            var id = $(el.target).parents('.clearfix').parent().attr('id');
            var chatWindows = replyModel.getChatWindows();
            var self = this;
            for (var iii = 0; iii < chatWindows.length; iii++) {
                if (id == chatWindows[iii]) {
                    replyModel.remove(chatWindows, iii);
                    document.getElementById(id).classList.remove("_show");
                    replyModel.calculateChatWindows();
                }
            }
        },
        /**
         * Close Chat Window
         */
        _closeChatWindow: function (element) {
            if (element.length) {
                $(element).parents('[id^="live-chat-"]').remove();
                replyModel.openWindowCount(replyModel.openWindowCount() - 1);
            }
            $('[id^="live-chat-"]').css('right', (20) + 'px');
        },
        /**
         * User Typing
         */
        userTyping: function (data) {
            // will be implement
        },
        /**
         * Get Loader Image
         */
        getLoaderImage: function () {
            return window.chatboxConfig.loaderImage;
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
        /**
         * Hide Emoji Box
         */
        hideEmojiBox: function (data, event) {
            $(event.currentTarget).removeClass('open');
        },
    });
});