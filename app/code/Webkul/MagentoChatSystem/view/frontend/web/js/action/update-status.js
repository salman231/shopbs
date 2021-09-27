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
    ],
    function ($, storage) {
        'use strict';

        return function (statusData, canChat, chatscreen, endchat) {
            var serviceUrl,
                payload;
            /**
             * Checkout for guest and registered customer.
             */
            serviceUrl = 'rest/V1/chat/change-status';
            payload = {
                status: statusData.status   
            };
            return storage.post(
                serviceUrl,
                JSON.stringify(payload)
            ).fail(
                function (response) {
                    console.log(response);
                }
            ).done(
                function (response) {
                    var data = $.parseJSON(response);
                    canChat(data.chat_status);
                    if(endchat) {
                        chatscreen(false);
                    }
                }
            );
        };
    }
);