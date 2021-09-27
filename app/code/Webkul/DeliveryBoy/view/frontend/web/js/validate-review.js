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
    'jquery/validate',
    'mage/translate'
], function ($) {
    'use strict';

    $.validator.addMethod(
        'rating-required', function (value) {
            return value !== undefined;
        }, $.mage.__('Please select one of each of the ratings above.'));
    $.validator.addMethod(
        'validate-comment', function (value) {
            return (!value || (typeof(value) === 'string' && value.split(' ').length >= 5));
        }, $.mage.__('Comment should contain at least 5 words.'));
});
