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

        return function (reportData) {
            var serviceUrl,
                payload;
            /**
             * save agent rating action.
             */
            serviceUrl = 'rest/V1/chat/report';
            payload = {
                report: {
                    agent_id: replyModel.receiverId(),
                    customer_name: replyModel.customerName(),
                    customer_id: replyModel.customerId(),
                    subject: reportData.subject,
                    content: reportData.content,
                }
            };
            return storage.post(
                serviceUrl,
                JSON.stringify(payload)
            );
        };
    }
);