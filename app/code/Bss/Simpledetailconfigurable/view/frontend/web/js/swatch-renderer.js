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
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
define([
    'jquery',
    'underscore',
    'jquery/ui',
    'jquery/jquery.parsequery'
], function ($, _) {
    'use strict';
    return function (widget) {

        $.widget('bss.SwatchRenderer', widget, {

            /**
             * Event for swatch options
             *
             * @param {Object} $this
             * @param {Object} $widget
             * @private
             */
            _OnClick: function ($this, $widget) {
                var options = $widget.options;
                if (!$widget.inProductList) {
                    var $parent = $this.parents('.' + $widget.options.classes.attributeClass),
                        $wrapper = $this.parents('.' + $widget.options.classes.attributeOptionsWrapper),
                        $label = $parent.find('.' + $widget.options.classes.attributeSelectedOptionLabelClass),
                        attributeId = $widget.getDataofAttr($parent, 'attribute-id'),
                        $input = $parent.find('.' + $widget.options.classes.attributeInput),
                        checkAdditionalData = JSON.parse(this.options.jsonSwatchConfig[attributeId]['additional_data']);

                    if ($widget.inProductList) {
                        $input = $widget.productForm.find(
                            '.' + $widget.options.classes.attributeInput + '[name="super_attribute[' + attributeId + ']"]'
                        );
                    }

                    if ($this.hasClass('disabled')) {
                        return;
                    }

                    if ($this.hasClass('selected')) {
                        $parent.removeAttr('data-option-selected option-selected').find('.selected').removeClass('selected');
                        $input.val('');
                        $label.text('');
                        $this.attr('aria-checked', false);
                    } else {
                        var optionId = $widget.getDataofAttr($this, 'option-id');
                        $parent.attr({'data-option-selected': optionId, 'option-selected': optionId}).find('.selected').removeClass('selected');
                        $label.text($widget.getDataofAttr($this, 'option-label'));
                        $input.val(optionId);
                        $input.attr('data-attr-name', this._getAttributeCodeById(attributeId));
                        $this.addClass('selected');
                        $widget._toggleCheckedAttributes($this, $wrapper);
                    }

                    $widget._Rebuild();

                    if ($widget.element.parents($widget.options.selectorProduct)
                        .find(this.options.selectorProductPrice).is(':data(mage-priceBox)')
                    ) {
                        $widget._UpdatePrice();
                    }

                    $(document).trigger('updateMsrpPriceBlock',
                        [
                            _.findKey($widget.options.jsonConfig.index, $widget.options.jsonConfig.defaultValues),
                            $widget.options.jsonConfig.optionPrices
                        ]);
                    if (!window.bssGallerySwitchStrategy || window.bssGallerySwitchStrategy != 'disabled') {
                        if (parseInt(checkAdditionalData['update_product_preview_image'], 10) === 1) {
                            $widget._loadMedia();
                        }
                    }

                    $input.trigger('change');
                }
                if ($widget.inProductList) {
                    $widget._super($this, $widget);
                    var childProductData = this.options.jsonConfig.bss_simple_detail;
                    if (!$.isEmptyObject(childProductData)
                        && childProductData && childProductData.child
                        && !$.isEmptyObject(childProductData.child)
                        && options.jsonConfig.is_enable_swatch_name
                    ) {
                        $widget._UpdateProductName($this);
                    }
                }
            },

            /**
             * Update product name
             *
             * @param ele
             * @private
             */
            _UpdateProductName: function (ele) {
                var index = '',
                    childProductData = this.options.jsonConfig.bss_simple_detail,
                    $productName,
                    $widget = this;

                ele.parents(".product-item-details").find(".super-attribute-select").each(function () {
                    var option_id = $widget.getDataofAttr($(this), "option-selected");
                    if (typeof option_id === "undefined" && $(this).val() !== "") {
                        option_id = $(this).val();
                    }
                    if (option_id !== null && $(this).val() !== "") {
                        index += option_id + '_';
                    }
                });

                if (!childProductData['child'].hasOwnProperty(index)) {
                    this._ResetName(ele);
                    return false;
                }

                $productName = childProductData['child'][index]['name'];
                if ($productName) {
                    ele.parents(".product-item-details").find('.product-item-link').text($productName);
                }
            },

            /**
             * Reset default product name
             * @param ele
             * @private
             */
            _ResetName: function (ele) {
                var childProductData = this.options.jsonConfig.bss_simple_detail,
                    productName = childProductData['child']['default'];
                if (productName) {
                    ele.parents(".product-item-details").find('.product-item-link').text(productName);
                }
            },

            /**
             * Get attribute value,
             * Compatible with M2.3x and M2.4
             * Reason: Some important attributes were changed format (data-attribute in stead of attribute)
             *
             * @param element
             * @param name
             * @returns {*}
             */
            getDataofAttr(element, name) {
                var attr = element.attr(name);
                if (undefined !== attr && attr && attr.length) {
                    return attr;
                }
                return element.data(name);
            }
        });

        return $.bss.SwatchRenderer;
    }
});
