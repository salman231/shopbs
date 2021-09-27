/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'ko',
    'uiComponent',
    'Magento_Checkout/js/model/quote',
    'Webkul_GiftCard/js/view/checkout/action/set-coupon-code',
    'Webkul_GiftCard/js/view/checkout/action/cancel-coupon'
], function ($, ko, Component, quote, setCouponCodeAction, cancelCouponAction) {
    'use strict';

    var totals = quote.getTotals(),
        couponCode = ko.observable(null),
        couponAmount = ko.observable(null),
        isApplied;
    if (totals()) {
       var  count = 0;
       $.each( totals().total_segments, function (key, val) {
       // totals().total_segments.each(function(){
          if(val.code == 'fee'){
            couponCode(val.title);
            couponAmount(-val.value);
            count = 1;
          }

        });
        if(count == 0){
            couponCode();
            couponAmount();
        }
      
    }
    isApplied = ko.observable(couponCode() != null);

    return Component.extend({
        defaults: {
            template: 'Webkul_GiftCard/checkout/giftcard-discount'
        },
        couponCode: couponCode,
        code:couponCode,
        couponamount:couponAmount,
        /**
         * Applied flag
         */
        isApplied: isApplied,

        /**
         * Coupon code application procedure
         */
        apply: function () {
        
            if (this.validate()) {
                setCouponCodeAction(couponCode(),couponAmount(), isApplied);
            }
        },

        /**
         * Cancel using coupon
         */
        cancel: function () {
            if (this.validate()) {
                couponCode('');
                couponAmount('');
                cancelCouponAction(isApplied);
            }
        },

        /**
         * Coupon form validation
         *
         * @returns {Boolean}
         */
        validate: function () {
            var form = '#discount-form-gift';

            return $(form).validation() && $(form).validation('isValid');
        }
    });
});
