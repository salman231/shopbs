define([
    'ko',
    'uiComponent',
    'Magento_Checkout/js/model/quote',
    'Magento_Catalog/js/price-utils',
    'Magento_Checkout/js/model/totals'

], function (ko, Component, quote, priceUtils, totals) {
    'use strict';
    var show_hide_Extrafee_blockConfig = window.checkoutConfig.show_hide_Extrafee_block;
    var fee_label = window.checkoutConfig.fee_label;
    var custom_fee_amount = window.checkoutConfig.custom_fee_amount;

    return Component.extend({

        totals: quote.getTotals(),
        canVisibleExtrafeeBlock: show_hide_Extrafee_blockConfig,
        getFormattedPrice: ko.observable(priceUtils.formatPrice(custom_fee_amount, quote.getPriceFormat())),
        getFeeLabel:ko.observable(fee_label),
        isDisplayed: function () {
            return this.getValue() != 0;
        },
        getValue: function () {
            var price = 0;
                if (this.totals() && ((custom_fee_amount != 0) && (custom_fee_amount != null))) {
                    price = custom_fee_amount;
                }
            return price;
        }
    });
});

