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
    'underscore',
    'priceUtils'
], function ($, _, utils) {
    'use strict';
    return function (widget) {

        /**
         * Custom option preprocessor
         * @param  {jQuery} element
         * @param  {Object} optionsConfig - part of config
         * @return {Object}
         */
        function defaultGetOptionValue(element, optionsConfig) {
            var changes = {},
                optionValue = element.val(),
                optionId = utils.findOptionId(element[0]),
                optionName = element.prop('name'),
                optionType = element.prop('type'),
                optionConfig = optionsConfig[optionId],
                optionHash = optionName;

            switch (optionType) {
                case 'text':
                case 'textarea':
                    changes[optionHash] = optionValue ? optionConfig.prices : {};
                    break;

                case 'radio':
                    if (element.is(':checked')) {
                        changes[optionHash] = optionConfig[optionValue] && optionConfig[optionValue].prices || {};
                    }
                    break;

                case 'select-one':
                    changes[optionHash] = optionConfig[optionValue] && optionConfig[optionValue].prices || {};
                    break;

                case 'select-multiple':
                    _.each(optionConfig, function (row, optionValueCode) {
                        optionHash = optionName + '##' + optionValueCode;
                        changes[optionHash] = _.contains(optionValue, optionValueCode) ? row.prices : {};
                    });
                    break;

                case 'checkbox':
                    optionHash = optionName + '##' + optionValue;
                    changes[optionHash] = element.is(':checked') ? optionConfig[optionValue].prices : {};
                    break;

                case 'file':
                    // Checking for 'disable' property equal to checking DOMNode with id*="change-"
                    changes[optionHash] = optionValue || element.prop('disabled') ? optionConfig.prices : {};
                    break;
            }

            return changes;
        }

        $.widget('bss.priceOptions', widget, {
            _create: function createPriceOptions() {
                this._super();
                var form = this.element,
                    options = $(this.options.optionsSelector, form),
                    priceBox = $(this.options.priceHolderSelector, $(this.options.optionsSelector).element),
                    self = this;

                if (priceBox.data('magePriceBox') &&
                    priceBox.priceBox('option') &&
                    priceBox.priceBox('option').priceConfig
                ) {
                    if (priceBox.priceBox('option').priceConfig.optionTemplate) {
                        this._setOption('optionTemplate', priceBox.priceBox('option').priceConfig.optionTemplate);
                    }
                    this._setOption('priceFormat', priceBox.priceBox('option').priceConfig.priceFormat);
                }

                $(document).on('bssPriceChange', function () {
                    var element = $(self.options.optionsSelector, form).filter(':hidden');
                    options = $(element).val('').trigger('change');
                });

                $(document).on('loadChildOption', function () {
                    options = $(self.options.optionsSelector, form);
                    self._applyOptionNodeFix(options);
                    options.on('change', self._onOptionChanged.bind(self));
                });

                this._applyOptionNodeFix(options);

                options.on('change', this._onOptionChanged.bind(this));
            },

            /**
             * Custom option change-event handler
             * @param {Event} event
             * @private
             */
            _onOptionChanged: function onOptionChanged(event) {
                var changes,
                    option = $(event.target),
                    handler = this.options.optionHandlers[option.data('role')];

                option.data('optionContainer', option.closest(this.options.controlContainer));

                if (handler && handler instanceof Function) {
                    changes = handler(option, this.options.optionConfig, this);
                } else {
                    changes = defaultGetOptionValue(option, this.options.optionConfig);
                }
                $(this.options.priceHolderSelector).trigger('updatePrice', changes);
            },
        });
        return $.bss.priceOptions;
    }
});
