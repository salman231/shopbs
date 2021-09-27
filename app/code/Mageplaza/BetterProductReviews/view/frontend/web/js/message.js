/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category  Mageplaza
 * @package   Mageplaza_BetterProductReviews
 * @copyright Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license   https://www.mageplaza.com/LICENSE.txt
 */

define(
    [], function () {
        "use strict";

        return {
            /**
             * Get error message html
             *
             * @param   messageText
             * @returns {string}
             */
            getErrorMessage: function (messageText) {
                return '<div class="message message-error error">' +
                    '<div data-ui-id="magento-framework-view-element-messages-0-message-error">' +
                    messageText +
                    '</div>' +
                    '</div>';
            },

            /**
             * Get info message html
             *
             * @param   messageText
             * @returns {string}
             */
            getInfoMessage: function (messageText) {
                return '<div class="message message-info info">' +
                    '<div data-ui-id="magento-framework-view-element-messages-0-message-info">' +
                    messageText +
                    '</div>' +
                    '</div>';
            },

            /**
             * Get success message html
             *
             * @param   messageText
             * @returns {string}
             */
            getSuccessMessage: function (messageText) {
                return '<div class="message message-success success">' +
                    '<div data-ui-id="magento-framework-view-element-messages-0-message-success">' +
                    messageText +
                    '</div>' +
                    '</div>';
            }
        }
    }
);
