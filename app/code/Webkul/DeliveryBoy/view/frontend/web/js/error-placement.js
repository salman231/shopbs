/**
 * Webkul Software.
 *
 * PHP version 7.0+
 *
 * @category  Webkul
 * @package   Webkul_DeliveryBoy
 * @author    Webkul <support@webkul.com>
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html ASL Licence
 * @link      https://store.webkul.com/license.html
 */
define([
    'jquery',
    'mage/mage'
], function ($) {
    'use strict';

    return function (config, form) {
        $(form).mage('validation', {
            /** @inheritdoc */
            errorPlacement: function (error, element) {
                if (element.attr('name') === 'rating') {
                    element.closest('.nested').closest('.control').after(error);
                } else {
                    element.after(error);
                }
            }
        });
    };
});
