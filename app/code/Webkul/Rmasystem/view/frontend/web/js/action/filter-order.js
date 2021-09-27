/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Rmasystem
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
/*jshint browser:true jquery:true*/
/*global alert*/
define(
    [
        'jquery',
        'mage/url',
    ],
    function ($, urlBuilder) {
        'use strict';

        return function (filterData) {
            var serviceUrl,
                payload;

            var contentType = 'application/json';
            
            /**
             * Checkout for guest and registered customer.
             */
            serviceUrl = 'rest/V1/apply/filter/';
            payload = {
                orderId: filterData.orderFilter,
                price: filterData.priceFilter,
                date: filterData.dateFilter
            };
            return $.ajax({
                url: urlBuilder.build(serviceUrl),
                type: 'POST',
                showLoader: true,
                data: JSON.stringify(payload),
                global: true,
                contentType: contentType
            });
        };
    }
);
