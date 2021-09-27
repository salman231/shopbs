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
    'Magento_Ui/js/view/messages',
], function ($, Component) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Webkul_DeliveryBoy/modal-messages',
            selector: '[data-role="deliveryboy-messages"]',
        }
    });
});
