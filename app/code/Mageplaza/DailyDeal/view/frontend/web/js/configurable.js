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
    'jquery',
    'underscore'
], function ($, _) {
    'use strict';

    $.widget('mageplaza.dailydeal_deal', {
        _create: function () {
            this.checkShowData();

            $('.old-price.sly-old-price.no-display').first().hide();
        },

        checkShowData: function () {
            var self = this;

            $("#product-options-wrapper").on('change', function () {
                var selectedOption = self.getSelectedOption();
                var childIdSelect  = self.getProductIdFromOptions(selectedOption);

                self.displayDeal(childIdSelect);
            });
        },

        getProductIdFromOptions: function (selectedOption) {
            var product_id_index = $('[data-role=swatch-options]').data('mageSwatchRenderer').options.jsonConfig.index;

            return _.findKey(product_id_index, selectedOption);
        },

        getSelectedOption: function () {
            var selected_options = {};

            $('div.swatch-attribute').each(function (k, attr) {
                var attribute_id    = $(attr).attr('attribute-id');
                var option_selected = $(attr).attr('option-selected');

                if (!attribute_id || !option_selected) {
                    return;
                }
                selected_options[attribute_id] = option_selected;
            });
            return selected_options;
        },

        displayDeal: function (childIdSelect) {
            var childIdsDeal    = this.options.childConfigurableProductIds;
            var dealUrl         = this.options.dealUrl;
            var dealBlock       = $('#mp-dailydeal-configurable-block'),
                labelImg        = $('.mpdailydeal-percent-above-image'),
                labelAfterPrice = $('.mpdailydeal-percent-underprice');

            if ($.inArray(childIdSelect, childIdsDeal) !== -1) {
                $.ajax({
                    url: dealUrl,
                    dataType: 'json',
                    cache: false,
                    data: {'id': childIdSelect},
                    showLoader: true,
                    success: function (result) {
                        dealBlock.html(result.success);
                        dealBlock.trigger('contentUpdated');
                    }
                });
            } else {
                dealBlock.html('');
                labelImg.hide();
                labelAfterPrice.hide();
            }
        }
    });

    return $.mageplaza.dailydeal_deal;
});
