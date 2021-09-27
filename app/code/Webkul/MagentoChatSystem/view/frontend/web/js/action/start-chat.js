/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MagentoChatSystem
 * @author    Webkul
 * @copyright Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
define(
    [
        'jquery',
        'Webkul_MagentoChatSystem/js/model/url-builder',
        'mage/storage',
        'Webkul_MagentoChatSystem/js/action/save-message',
        'Webkul_MagentoChatSystem/js/model/reply',
        'mage/translate'
    ],
    function ($, urlBuilder, storage, saveMessageAction, replyModel) {
        'use strict';

        return function (customerData, canChat) {
            var serviceUrl,
                payload;

            /**
             * Checkout for guest and registered customer.
             */
            serviceUrl = 'rest/V1/customer/info-save';
            payload = {
                message: customerData.message,
                agentId: customerData.agent_id,
                agentUniqueId: customerData.agent_unique_id
            };
            replyModel.loadingState($.mage.__('intializing chat...'));
            return storage.post(
                serviceUrl,
                JSON.stringify(payload)
            ).fail(
                function (response) {

                }
            ).done(
                function (response) {
                    var data = $.parseJSON(response);
                    if (data.message !== 'undefined' &&
                        data.message !== '' &&
                        customerData.agent_unique_id !== 0
                    ) {
                        data.dateTime = customerData.dateTime;
                        data.receiver_id = customerData.agent_id;
                        data.receiver_unique_id = customerData.agent_unique_id
                        canChat(data.chat_status);
                    }
                }
            );
        };
    }
);