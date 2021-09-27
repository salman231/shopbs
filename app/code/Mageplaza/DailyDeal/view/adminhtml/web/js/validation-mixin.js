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
 * @package     Mageplaza_DailyDeal
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

define(['jquery'], function ($) {
    "use strict";

    return function () {
        $.validator.addMethod(
            'validate-deal-price',
            function () {
                var originalPrice = Number($('#deal_original_price').text().replace(/[^0-9.-]+/g, "")),
                    dealPrice = Number($('#deal_deal_price').val().replace(/[^0-9.-]+/g, ""));

                if (dealPrice < 0) {
                    return false;
                }

                if (originalPrice >= dealPrice) {
                    return true;
                }
            },
            $.mage.__('Deal price must be less than or equal to original price and greater than 0')
        );

        $.validator.addMethod(
            'validate-deal-qty',
            function () {
                var productQty = Number($('#deal_product_qty').text().replace(/[^0-9.-]+/g, "")),
                    dealQty = Number($('#deal_deal_qty').val().replace(/[^0-9.-]+/g, ""));

                if (dealQty <= productQty) {
                    return true;
                }
            },
            $.mage.__('Deal qty must be less than or equal to product qty')
        );
    };
});