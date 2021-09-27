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
    'Magento_Ui/js/modal/modal',
    'mage/translate',
    'Magento_Catalog/js/price-utils'
], function ($, modal, $t, priceUtils) {
    "use strict";

    $.widget('mageplaza.dailydeal', {
        popup: false,

        /**
         * This method constructs a new widget.
         * @private
         */
        _create: function () {
            this.initProductsGrid();
            this.initPopup();
            this.selectProduct();
            this.initDiscount();
            this.initSaleQty();
        },

        /**
         * Init popup
         * Popup will automatic open
         */
        initPopup: function () {
            var options;

            if (!this.popup) {
                options = {
                    type: 'popup',
                    responsive: true,
                    innerScroll: true,
                    title: $t('Select Product'),
                    buttons: [{
                        text: $t('Continue'),
                        class: '',
                        click: function () {
                            this.closeModal();
                        }
                    }]
                };

                this.popup = modal(options, $('#mpdailydeal-products-grid'));
            }

            return this.popup;
        },

        /**
         * Init select product
         */
        selectProduct: function () {
            var self = this;

            $('body').delegate('#product_grid_table tbody tr', 'click', function () {
                var el        = $(this);
                var prodId    = el.find('.col-id').text().trim();
                var prodName  = el.find('.col-name').text().trim();
                var prodSku   = el.find('.col-sku').text().trim();
                var prodPrice = el.find('.col-price').text().trim();
                var prodQty   = el.find('.col-barcode_qty').text().trim();

                el.find('input').attr('checked', 'checked');

                $("#deal_product_id").val(prodId);
                $("#deal_product_name").val(prodName);
                $("#deal_product_sku").val(prodSku);
                $("#deal_original_price").text(prodPrice);
                $("#deal_product_qty").text(prodQty);
                $(".modal-title").text(prodName);

                self.initDiscount();
            });
        },

        /**
         * Init products grid
         */
        initProductsGrid: function () {
            var self = this;

            $("#load-product-grid").click(function () {
                $.ajax({
                    method: 'POST',
                    url: self.options.url,
                    data: {form_key: window.FORM_KEY},
                    showLoader: true
                }).done(function (response) {
                    $('#mpdailydeal-products-grid').html(response);
                    self.popup.openModal();
                });
            });
        },

        /**
         * Show Qty of sold items
         */
        initSaleQty: function () {
            var label   = $('#deal_sale_qty_label'),
                saleQty = parseFloat($('#deal_sale_qty').val()),
                dealQty = parseFloat($('#deal_deal_qty').val());

            if (!saleQty) {
                label.text(0);
            } else if (dealQty <= saleQty) {
                label.text($t('Sold Out'));
            } else {
                label.text(saleQty);
            }
        },

        /**
         * @param str
         * @return {number}
         */
        extractPrice: function (str) {
            return Number(str.replace(/[^0-9.-]+/g, ""));
        },

        /**
         * show discount on discount field
         */
        initDiscount: function () {
            var originalPrice = this.extractPrice($('#deal_original_price').text()),
                dealPrice     = this.extractPrice($('#deal_deal_price').val()),
                discount      = this.getDiscount(originalPrice, dealPrice);

            $('#deal_discount').text(discount);
            this.changeDiscount();
        },

        /**
         * Event keyup deal price input
         * @private
         */
        changeDiscount: function () {
            var self = this;

            $("input#deal_deal_price").keyup(function () {
                var input         = $(this),
                    originalPrice = self.extractPrice($('#deal_original_price').text()),
                    discount      = self.getDiscount(originalPrice, input.val());

                $('#deal_discount').text(discount);
            });
        },

        /**
         * Get text discount and percent
         * @param originalPrice
         * @param dealPrice
         * @returns {*}
         */
        getDiscount: function (originalPrice, dealPrice) {
            var discount, percent;

            if (originalPrice !== 0) {

                discount = originalPrice - dealPrice;
                percent  = parseFloat(discount / originalPrice * 100).toFixed(2) + "%";

                return priceUtils.formatPrice(discount) + ' (-' + percent + ')';
            }

            return 0;
        }
    });

    return $.mageplaza.dailydeal;
});
