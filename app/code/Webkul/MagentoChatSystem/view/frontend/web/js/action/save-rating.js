/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MagentoChatSystem
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
define(
    [
        'jquery',
        'mage/storage',
        'Webkul_MagentoChatSystem/js/model/reply'
    ],
    function ($, storage, replyModel) {
        'use strict';

        return function (ratingData) {
            var serviceUrl,
                payload;
            /**
             * save agent rating action.
             */
            serviceUrl = 'rest/V1/chat/agentrating';
            payload = {
                agentRating: {
                    agent_id: replyModel.receiverId(),
                    customer_id: replyModel.customerId(),
                    agent_unique_id: replyModel.receiverUniqueId(),
                    rating: ratingData.rating,
                    rating_comment: ratingData.rating_comment,
                    status: 0
                }
            };
            return storage.post(
                serviceUrl,
                JSON.stringify(payload)
            );
        };
    }
);