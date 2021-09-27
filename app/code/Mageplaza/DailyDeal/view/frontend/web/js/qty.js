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

define([
    'jquery'
], function ($) {
    'use strict';

    var initializeQty = function (remainQty, soldQty, element) {
        var itemLeft    = $('.remaining-qty-items', element).find('.count-items'),
            itemSold    = $('.sold-qty-items', element).find('.count-items'),
            floatRemain = $('.float-remain', element).find('.widget-qty'),
            floatSold   = $('.float-sold', element).find('.widget-qty');

        itemLeft.html(remainQty);
        itemSold.html(soldQty);
        floatRemain.html(remainQty);
        floatSold.html(soldQty);
    };

    return function (data, element) {
        var productId       = data.prodId,
            remainQty       = data.qtyRemain,
            soldQty         = data.qtySold,
            isSimpleProduct = data.isSimpleProduct;

        if (isSimpleProduct) {
            $.ajax({
                url: data.qtyUrl,
                dataType: 'json',
                data: {'id': productId},
                cache: false,
                success: function (res) {
                    remainQty = res.qtyRemain;
                    soldQty   = res.qtySold;

                    initializeQty(remainQty, soldQty, element);
                }
            });
        } else {
            remainQty = this.options.qtyRemain;
            soldQty   = this.options.qtySold;

            initializeQty(remainQty, soldQty, element);
        }
    };

});