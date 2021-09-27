/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Customer store credit(balance) application
 */
define([
    'ko',
    'jquery',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/resource-url-manager',
    'Magento_Checkout/js/model/error-processor',
    'Magento_SalesRule/js/model/payment/discount-messages',
    'mage/storage',
    'mage/translate',
    'Magento_Checkout/js/action/get-payment-information',
    'Magento_Checkout/js/model/totals',
    'Magento_Checkout/js/model/full-screen-loader'
], function (ko, $, quote, urlManager, errorProcessor, messageContainer, storage, $t, getPaymentInformationAction,
    totals, fullScreenLoader
) {
    'use strict';

    return function (couponCode,couponAmount, isApplied) {
        var quoteId = quote.getQuoteId(),
            url = 'giftcard/giftcard/updategiftcard',
            message = $t('Your coupon was successfully applied.');
            fullScreenLoader.startLoader();

        return storage.put(
            url,
            {amount:couponAmount,code:couponCode},
            false
        ).done(function (response) {
            var deferred;

            if (response) {
                deferred = $.Deferred();

                isApplied(true);
                totals.isLoading(true);
                getPaymentInformationAction(deferred);
                $.when(deferred).done(function () {
                    fullScreenLoader.stopLoader();
                    totals.isLoading(false);
                });
                if(response.code == 1){
                    messageContainer.addSuccessMessage({
                        'message': response.message
                    });
                }else{
                    messageContainer.addErrorMessage({
                        'message': response.message
                    });
                }
            }
        }).fail(function (response) {
            fullScreenLoader.stopLoader();
            totals.isLoading(false);
            errorProcessor.process(response, messageContainer);
        });
    };
});
