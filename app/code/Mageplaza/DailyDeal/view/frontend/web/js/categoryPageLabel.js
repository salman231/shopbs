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

    $.widget('mageplaza.dailydeal_deal_categoryLabel', {
        _create: function () {
            var product = $('.item.product.product-item'),
                labelData = this.options.labelData,
                labelHtml = this.options.labelHtml,
                position = this.options.position;

            product.each(function (k, elm) {
                var el = $(elm);
                var productId = el.find('.price-box.price-final_price').attr('data-product-id');
                var percentText;
                var imgEl;

                if (labelData.hasOwnProperty(productId)) {
                    percentText = labelData[productId];

                    if (position === 'price') {
                        el.find('.price-box.price-final_price').append(labelHtml);
                        el.find('.mpdailydeal-percent-underprice').append('<span>' + percentText + '</span>');
                    } else {
                        imgEl = el.find('.product-image-wrapper');

                        if (!imgEl.length) {
                            imgEl = el.find('.product.photo.product-item-photo');
                        }
                        imgEl.append(labelHtml);
                        el.find('.mpdailydeal-percent-cat-above-image').append(percentText).css('z-index', 10);
                    }

                }
            });
        }
    });

    return $.mageplaza.dailydeal_deal_categoryLabel;
});
