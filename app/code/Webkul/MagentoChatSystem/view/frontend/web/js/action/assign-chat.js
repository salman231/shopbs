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
        'Webkul_MagentoChatSystem/js/action/start-chat',
        'Webkul_MagentoChatSystem/js/model/reply',
        'Webkul_MagentoChatSystem/js/model/manage-rating'
    ],
    function ($, urlBuilder, storage, startChatAction, replyModel, ratingModel) {
        'use strict';

        return function (customerData, canChat) {
            var serviceUrl,
                payload;

            /**
             * find available aganet and assign chat.
             */
            serviceUrl = 'rest/V1/chat/assign-chat';
            payload = {
                customerId: customerData.customer_id,
                uniqueId: customerData.unique_id
            };
            replyModel.loadingState($.mage.__('Assigning agent for you...'));
            return storage.post(
                serviceUrl,
                JSON.stringify(payload)
            ).fail(
                function (response) {
                    console.log('error during assign data');
                }
            ).done(
                function (response) {
                    var data = $.parseJSON(response);
                    if (data.error == false) {
                        canChat(1);
                        customerData.agent_id = data.agent_id;
                        customerData.agent_unique_id = data.agent_unique_id;
                        replyModel.receiverUniqueId(data.agent_unique_id);
                        replyModel.receiverId(data.agent_id);
                        replyModel.receiverName(data.agent_name);
                        replyModel.receiverEmail(data.email);
                    } else {
                        replyModel.receiverUniqueId(data.agent_unique_id);
                        replyModel.receiverId(data.agent_id);
                        replyModel.receiverName(data.agent_name);
                        replyModel.receiverEmail(data.email);
                        replyModel.agentGoesOff(true);
                        replyModel.agentGoesOffError(data.message);
                    }
                    ratingModel.ratingData(data.agentRatings)
                    replyModel.adminStatus(data.agent_status);
                    /*fullScreenLoader.stopLoader();*/
                }
            );
        };
    }
);