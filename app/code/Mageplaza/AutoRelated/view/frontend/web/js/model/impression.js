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
 * @category    Mageplaza
 * @package     Mageplaza_AutoRelated
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

define(['jquery', 'mage/storage', 'rjsResolver'], function ($, storage, resolver) {
    'use strict';

    var ruleIds = [];

    /**
     * @type {{initialize: initialize, registerRuleImpression: registerRuleImpression, afterResolveDocument: afterResolveDocument}}
     */
    var model = {
        /**
         * initialize model
         */
        initialize: function () {
            resolver(this.afterResolveDocument.bind(this));
        },

        /**
         * @param ruleId
         */
        registerRuleImpression: function (ruleId) {
            if ($.inArray(ruleId, ruleIds) === -1) {
                ruleIds.push(ruleId);
            }
        },

        /**
         * Call impression action
         */
        afterResolveDocument: function () {
            if (ruleIds.length) {
                storage.post('autorelated/ajax/impression', JSON.stringify({ruleIds: ruleIds}), false)
            }
        }
    };

    model.initialize();

    return model;
});