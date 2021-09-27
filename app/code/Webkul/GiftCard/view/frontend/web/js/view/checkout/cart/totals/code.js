/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'Magento_Checkout/js/view/summary/abstract-total',
    'Magento_Checkout/js/model/quote'
], function ($,Component, quote) {
    'use strict';
    
    // var totals = quote.getTotals();
    return Component.extend({
        defaults: {
            template: 'Webkul_GiftCard/checkout/cart/totals/fee'
        },
        totals: quote.getTotals(),

        /**
         * @return {*|Boolean}
         */
        isDisplayed: function () {
            var seg = this.totals().total_segments;
            var  count = 0;
            var pureValue = this.getPureValue();
            $.each( seg, function (key, val) {
                if(val.code == 'fee' && !isNaN(pureValue) && pureValue!=0){
                    count = 1;
                }
            });
            if(count == 0){
                return false;
            }else{
                return true;
            }
        },
        /**
         * @return {*}
         */
        getCouponCode: function () {
            if (!this.totals()) {
                return null;
            }

            return '';
        },

       
        /**
         * Get discount title
         *
         * @returns {null|String}
         */
        getTitle: function () {
            var discountSegments;

            if (!this.totals()) {
                return null;
            }

            discountSegments = this.totals()['total_segments'].filter(function (segment) {
                return segment.code.indexOf('discount') !== -1;
            });

            return discountSegments.length ? discountSegments[0].title : null;
        },

        /**
         * @return {Number}
         */
        getPureValue: function () {
            var price = 0;
            $.each( this.totals().total_segments, function (key, val) {
                if(val.code == 'fee'){
                    price = parseFloat(val.value);
                }
        
            });
            return price;
        },

        /**
         * @return {*|String}
         */
        getValue: function () {
            return this.getFormattedPrice(this.getPureValue());
        }
    });
});
