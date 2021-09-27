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
    "jquery",
    "../socket.io",
    "jquery/ui",
    "mage/translate"
], function ($, io) {
    'use strict';
    $.widget('mage.startStopServer', {
        options: {},
        _create: function () {
            var self = this;
            //start node server
            $(self.options.startButton).on('click', function () {
                self._serverStart();
            });
            //stop node server
            $(self.options.stopButton).on('click', function () {
                self._serverStop();
            });
        },
        /**
         * Server Start
         */
        _serverStart: function () {
            var self = this;
            var hostName = $(self.options.configForm + ' #chatsystem_chat_config_host_name').val();
            var port = $(self.options.configForm + ' #chatsystem_chat_config_port_number').val();
            new Ajax.Request(self.options.startUrl, {
                method: 'post',
                data: {
                    form_key: window.FORM_KEY
                },
                parameters: {
                    hostname: hostName,
                    port: port
                },
                onSuccess: function (transport) {
                    var response = $.parseJSON(transport.responseText);
                    if (response.error) {
                        $('<div />').html(response.message)
                            .modal({
                                title: $.mage.__('Server Status'),
                                autoOpen: true,
                                buttons: [{
                                    text: 'OK',
                                    attr: {
                                        'data-action': 'cancel'
                                    },
                                    'class': 'action-primary',
                                    click: function () {
                                        this.closeModal();
                                    }
                                }]
                            });
                    } else {
                        location.reload();
                    }
                }
            });
        },
        /**
         * Server Stop
         */
        _serverStop: function () {
            var self = this;
            var hostName = $(self.options.configForm + ' #chatsystem_chat_config_host_name').val();
            var port = $(self.options.configForm + ' #chatsystem_chat_config_port_number').val();
            new Ajax.Request(self.options.stopUrl, {
                method: 'post',
                data: {
                    form_key: window.FORM_KEY
                },
                parameters: {
                    hostname: hostName,
                    port: port
                },
                onSuccess: function (transport) {
                    var response = $.parseJSON(transport.responseText);
                    if (response.error) {
                        $('<div />').html(response.message)
                            .modal({
                                title: $.mage.__('Server Status'),
                                autoOpen: true,
                                buttons: [{
                                    text: 'OK',
                                    attr: {
                                        'data-action': 'cancel'
                                    },
                                    'class': 'action-primary',
                                    click: function () {
                                        this.closeModal();
                                    }
                                }]
                            });
                    }
                    location.reload();
                }
            });
        },

    });
    return $.mage.startStopServer;
});