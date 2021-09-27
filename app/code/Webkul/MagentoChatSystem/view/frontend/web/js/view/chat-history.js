/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MagentoChatSystem
 * @author    Webkul
 * @copyright Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
 /*jshint jquery:true*/
define([
    "jquery",
    'mage/translate',
    'Magento_Ui/js/modal/alert',
    "jquery/ui",
    'mage/calendar',
], function ($, $t, alert) {
    'use strict';
    $.widget('mage.chatHistory', {
        _create: function () {
            var self = this;
            $("#special-from-date").calendar({'dateFormat':'Y-m-d'});
        }

    });
    return $.mage.chatHistory;
});