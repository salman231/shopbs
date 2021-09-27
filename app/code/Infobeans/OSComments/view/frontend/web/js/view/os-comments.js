define([
    'uiComponent',
], function (Component) {
    'use strict';

    var disabled = window.checkoutConfig.shipping.disabled;
    var shippingHeading = window.checkoutConfig.shipping.shippingHeading;
    if (disabled == 0) {
        return Component.extend({
            initialize: function () {
                this._super();
                return this;
            }
        });
    }
    return Component.extend({
        initialize: function () {
            this._super();
            return this;
        },
        defaults: {
            template: 'Infobeans_OSComments/os-comments'
        },
        getDeliveryLabel: function () {
            return shippingHeading;
        },
        getDeliveryComments: function () {
            return window.checkoutConfig.quoteData.delivery_comment;
        }
    });
});
