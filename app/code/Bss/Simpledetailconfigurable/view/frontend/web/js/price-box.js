/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_Simpledetailconfigurable
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

define([
    'jquery',
    'Magento_Catalog/js/price-utils',
    'underscore',
    'mage/template',
    'jquery-ui-modules/widget'
], function ($, utils, _, mageTemplate) {
    'use strict';

    var globalOptions = {
        productId: null,
        priceConfig: null,
        prices: {},
        priceTemplate: '<span class="price"><%- data.formatted %></span>'
    };

    $.widget('mage.priceBox', {
        options: globalOptions,

        /**
         * Widget initialisation.
         * Every time when option changed prices also can be changed. So
         * changed options.prices -> changed cached prices -> recalculation -> redraw price box
         */
        _init: function initPriceBox() {
            var box = this.element;

            box.trigger('updatePrice');
            this.cache.displayPrices = utils.deepClone(this.options.prices);
        },

        /**
         * Widget creating.
         */
        _create: function createPriceBox() {
            var box = this.element;

            this.cache = {};
            this._setDefaultsFromPriceConfig();
            this._setDefaultsFromDataSet();

            box.on('reloadPrice', this.reloadPrice.bind(this));
            box.on('updatePrice', this.onUpdatePrice.bind(this));
            box.trigger('price-box-initialized');
        },

        /**
         * Call on event updatePrice. Proxy to updatePrice method.
         * @param {Event} event
         * @param {Object} prices
         */
        onUpdatePrice: function onUpdatePrice(event, prices) {
            return this.updatePrice(prices);
        },

        /**
         * Updates price via new (or additional values).
         * It expects object like this:
         * -----
         *   "option-hash":
         *      "price-code":
         *         "amount": 999.99999,
         *         ...
         * -----
         * Empty option-hash object or empty price-code object treats as zero amount.
         * @param {Object} newPrices
         */
        updatePrice: function updatePrice(newPrices) {
            var prices = this.cache.displayPrices,
                additionalPrice = {},
                pricesCode = [],
                priceValue, origin, finalPrice;

            this.cache.additionalPriceObject = this.cache.additionalPriceObject || {};

            if (window.child_product_price) {
              this.cache.additionalPriceObject = {}
              window.child_product_price = false
            }
            if (newPrices) {
                $.extend(this.cache.additionalPriceObject, newPrices);
            }

            if (!_.isEmpty(additionalPrice)) {
                pricesCode = _.keys(additionalPrice);
            } else if (!_.isEmpty(prices)) {
                pricesCode = _.keys(prices);
            }

            var child_option = {};
            var child_option_price = {};
            var all_price = this.cache.additionalPriceObject;
            var bss_option = false;
            if (JSON.stringify(all_price).indexOf('options') !== -1) {
                bss_option = true;
            }
            if ($('.bss-price-option-child-product').length) {
                $('.bss-price-option-child-product').each(function(){
                    child_option = $(this).data('option-id');
                    var additionalPrice_child = {};
                    var productchildId = 0;
                    _.each(child_option, function (val, key) {
                        productchildId = val;
                        var additional1 = all_price[key];
                        if (additional1 && !_.isEmpty(additional1)) {
                            pricesCode = _.keys(additional1);

                            _.each(pricesCode, function (priceCode) {
                                priceValue = additional1[priceCode] || {};
                                priceValue.amount = +priceValue.amount || 0;

                                additionalPrice_child[priceCode] = additionalPrice_child[priceCode] || {
                                    'amount': 0
                                };
                                additionalPrice_child[priceCode].amount =  0 + (additionalPrice_child[priceCode].amount || 0) +
                                    priceValue.amount;
                            });
                        }
                    });

                    child_option_price[productchildId] = additionalPrice_child;
                })
            }

            _.each(this.cache.additionalPriceObject, function (additional) {
                if (additional && !_.isEmpty(additional)) {
                    pricesCode = _.keys(additional);
                }
                _.each(pricesCode, function (priceCode) {
                    priceValue = additional[priceCode] || {};
                    priceValue.amount = +priceValue.amount || 0;
                    priceValue.adjustments = priceValue.adjustments || {};

                    additionalPrice[priceCode] = additionalPrice[priceCode] || {
                        'amount': 0,
                        'adjustments': {}
                    };
                    additionalPrice[priceCode].amount =  0 + (additionalPrice[priceCode].amount || 0) +
                        priceValue.amount;
                    _.each(priceValue.adjustments, function (adValue, adCode) {
                        additionalPrice[priceCode].adjustments[adCode] = 0 +
                            (additionalPrice[priceCode].adjustments[adCode] || 0) + adValue;
                    });
                });
            });

            var additionalPrice_child1 = {};
            if (!_.isEmpty(child_option_price)) {
                _.each(child_option_price, function (option, childId) {
                    var additional2 = option;
                    if (!_.isEmpty(additional2)) {
                        pricesCode = _.keys(additional2);

                        _.each(pricesCode, function (priceCode) {
                            var priceValue2 = additional2[priceCode] || {};
                            priceValue2.amount = +priceValue2.amount || 0;

                            additionalPrice_child1[priceCode] = additionalPrice_child1[priceCode] || {
                                'amount': 0
                            };
                            additionalPrice_child1[priceCode].amount =  0 + (additionalPrice_child1[priceCode].amount || 0) +
                                priceValue2.amount;
                            if ($('#bss-option-price-'+ childId).length > 0) {
                                if (priceCode == 'finalPrice') {
                                    $('#bss-option-price-'+ childId).val(priceValue2.amount);
                                }
                                if (priceCode == 'basePrice') {
                                    $('#bss-option-price-'+ childId).attr('data-excltax-price', priceValue2.amount);
                                }
                            }
                        });
                    } else {
                        if ($('#bss-option-price-'+ childId).length > 0) {
                            $('#bss-option-price-'+ childId).val(0);
                            $('#bss-option-price-'+ childId).attr('data-excltax-price', 0);
                        }
                    }
                })
            }


            if (_.isEmpty(additionalPrice)) {
                this.cache.displayPrices = utils.deepClone(this.options.prices);
            } else {
                var addprices = {finalPrice:0,basePrice:0};
                var price_notuse = 0;
                if (!_.isEmpty(this.cache.additionalPriceObject.prices)) {
                    addprices.finalPrice = this.cache.additionalPriceObject.prices['finalPrice'].amount;
                    addprices.basePrice = this.cache.additionalPriceObject.prices['basePrice'].amount;
                }

                if ($('.bss-child-option.bss-hidden').length) {
                    $('.bss-child-option.bss-hidden').find('.bss-price-option-child-product').each(function(){
                        if ($(this).val()) {
                            price_notuse += parseFloat($(this).val());
                        }
                    })
                }

                _.each(additionalPrice, function (option, priceCode) {
                    origin = this.options.prices[priceCode] || {};
                    finalPrice = prices[priceCode] || {};
                    option.amount = option.amount || 0;
                    origin.amount = origin.amount || 0;
                    origin.adjustments = origin.adjustments || {};
                    finalPrice.adjustments = finalPrice.adjustments || {};

                    finalPrice.amount = 0 + origin.amount + option.amount - price_notuse;
                    _.each(option.adjustments, function (pa, paCode) {
                        finalPrice.adjustments[paCode] = 0 + (origin.adjustments[paCode] || 0) + pa;
                    });

                    if (bss_option && $('#bss-option-price').length > 0 && $('.product-custom-option').length > 0) {
                        if (priceCode == 'finalPrice') {
                            if (!_.isEmpty(additionalPrice_child1)) {
                                $('#bss-option-price').val(option.amount - additionalPrice_child1['finalPrice'].amount - addprices.finalPrice);
                            } else {
                                $('#bss-option-price').val(option.amount - addprices.finalPrice);
                            }
                        }
                        if (priceCode == 'basePrice') {
                             if (!_.isEmpty(additionalPrice_child1)) {
                                $('#bss-option-price').attr('data-excltax-price', option.amount - additionalPrice_child1['basePrice'].amount - addprices.basePrice);
                            } else {
                                $('#bss-option-price').attr('data-excltax-price', option.amount - addprices.basePrice);
                            }
                        }
                    }
                }, this);
            }

            if ($('#bss-option-price').val()) {
                $('#bss-option-price').trigger('change');
            }
            this.element.trigger('reloadPrice');
        },

        /*eslint-disable no-extra-parens*/
        /**
         * Render price unit block.
         */
        reloadPrice: function reDrawPrices() {
            var priceFormat = (this.options.priceConfig && this.options.priceConfig.priceFormat) || {},
                priceTemplate = mageTemplate(this.options.priceTemplate);

            _.each(this.cache.displayPrices, function (price, priceCode) {
                price.final = _.reduce(price.adjustments, function (memo, amount) {
                    return memo + amount;
                }, price.amount);

                price.formatted = utils.formatPrice(price.final, priceFormat);

                $('[data-price-type="' + priceCode + '"]', this.element).html(priceTemplate({
                    data: price
                }));
            }, this);
        },

        /*eslint-enable no-extra-parens*/
        /**
         * Overwrites initial (default) prices object.
         * @param {Object} prices
         */
        setDefault: function setDefaultPrices(prices) {
            this.cache.displayPrices = utils.deepClone(prices);
            this.options.prices = utils.deepClone(prices);
        },

        /**
         * Custom behavior on getting options:
         * now widget able to deep merge of accepted configuration.
         * @param  {Object} options
         * @return {mage.priceBox}
         */
        _setOptions: function setOptions(options) {
            $.extend(true, this.options, options);

            if ('disabled' in options) {
                this._setOption('disabled', options.disabled);
            }

            return this;
        },

        /**
         * setDefaultsFromDataSet
         */
        _setDefaultsFromDataSet: function _setDefaultsFromDataSet() {
            var box = this.element,
                priceHolders = $('[data-price-type]', box),
                prices = this.options.prices;

            this.options.productId = box.data('productId');

            if (_.isEmpty(prices)) {
                priceHolders.each(function (index, element) {
                    var type = $(element).data('priceType'),
                        amount = parseFloat($(element).data('priceAmount'));

                    if (type && !_.isNaN(amount)) {
                        prices[type] = {
                            amount: amount
                        };
                    }
                });
            }
        },

        /**
         * setDefaultsFromPriceConfig
         */
        _setDefaultsFromPriceConfig: function _setDefaultsFromPriceConfig() {
            var config = this.options.priceConfig;

            if (config && config.prices) {
                this.options.prices = config.prices;
            }
        }
    });

    return $.mage.priceBox;
});
