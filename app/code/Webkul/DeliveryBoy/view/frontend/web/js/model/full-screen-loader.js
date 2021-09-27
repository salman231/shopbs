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
    'rjsResolver'
], function ($, resolver) {
    'use strict';

    return {
        containerSelector: '.deliveryboy-review-wrapper',
        
        setContainerSelector: function (containerSelector) {
            this.containerSelector = containerSelector;
        },
        
        /**
         * Start full page loader action
         */
        startLoader: function () {
            $(this.containerSelector).trigger('processStart');
        },

        /**
         * Stop full page loader action
         *
         * @param {Boolean} [forceStop]
         */
        stopLoader: function (forceStop) {
            var $elem = $(this.containerSelector),
                stop = $elem.trigger.bind($elem, 'processStop');

            forceStop ? stop() : resolver(stop);
        }
    };
});
