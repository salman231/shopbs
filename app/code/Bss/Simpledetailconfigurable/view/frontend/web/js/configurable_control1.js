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
    'mage/template',
    'mage/translate',
    'priceUtils',
    'mage/url',
    'priceBox',
    'jquery/ui',
    'jquery/jquery.parsequery'
], function ($, _, mageTemplate, $t, priceUtils, urlBuilder) {
    'use strict';

    const IMAGE_CONFIG_REPLACE = 'replace';
    const IMAGE_CONFIG_PREPEND = 'prepend';
    const IMAGE_CONFIG_DISABLED = 'disabled';

    $.widget('mage.configurable', {
        options: {
            superSelector: '.super-attribute-select',
            selectSimpleProduct: '[name="selected_configurable_option"]',
            priceHolderSelector: '.price-box',
            spConfig: {},
            state: {},
            priceFormat: {},
            optionTemplate: '<%- data.label %>' +
                '<% if (typeof data.finalPrice.value !== "undefined") { %>' +
                ' <%- data.finalPrice.formatted %>' +
                '<% } %>',
            mediaGallerySelector: '[data-gallery-role=gallery-placeholder]',
            mediaGalleryInitial: null,
            slyOldPriceSelector: '.sly-old-price',
            normalPriceLabelSelector: '.normal-price .price-label',

            /**
             * Defines the mechanism of how images of a gallery should be
             * updated when user switches between configurations of a product.
             *
             * As for now value of this option can be either 'replace' or 'prepend'.
             *
             * @type {String}
             */
            gallerySwitchStrategy: 'replace',
            tierPriceTemplateSelector: '#tier-prices-template',
            tierPriceBlockSelector: '[data-role="tier-price-block"]',
            tierPriceTemplate: '',
            sdcp_classes: {
                sku: '.product.attribute.sku .value',
                name: '.page-title .base',
                fullDesc: {
                    label: '#tab-label-product\\.info\\.description',
                    content: '.bss-sdcp-description',
                    blockContent: '#product\\.info\\.description'
                },
                shortDesc: '.product.attribute.overview',
                stock: '.stock.available span',
                addtocart_button: '#product-addtocart-button',
                increment: '.product.pricing',
                qty_box: '#qty',
                tier_price: '.prices-tier.items',
                additionalInfo: {
                    label: '#tab-label-additional',
                    content: '#additional'
                },
                hiddenTab: 'bss-tab-hidden',
                customOption: '#bss-custom-option',
            },
            selectorProduct: '.product-info-main',
            productOptions: {},
            checkLocation : window.location.href
        },

        /**
         * Creates widget
         * @private
         */
        _create: function () {
            // Initial setting of various option values
            this._initializeOptions();

            // Override defaults with URL query parameters and/or inputs values
            this._overrideDefaults();

            // Change events to check select reloads
            this._setupChangeEvents();

            // Fill state
            this._fillState();

            // Setup child and prev/next settings
            this._setChildSettings();


            // Setup/configure values to inputs
            this._configureForValues();

            $(this.element).trigger('configurable.initialized');
            this._UpdateSelected(this.options, this);
        },

        /**
         * Initialize tax configuration, initial settings, and options values.
         * @private
         */
        _initializeOptions: function () {

            if (this.options.jsonModuleConfig['meta_data'] > 0) {
                this.options.jsonChildProduct['meta_data']['meta_title'] =
                    (this.options.jsonChildProduct['meta_data']['meta_title'] == null) ?
                        document.title :
                        this.options.jsonChildProduct['meta_data']['meta_title'];

                this.options.jsonChildProduct['meta_data']['meta_description'] =
                    (this.options.jsonChildProduct['meta_data']['meta_description'] == null) ?
                        $('head meta[name="description"]').attr('content') :
                        this.options.jsonChildProduct['meta_data']['meta_description'];

                if ($('head meta[name="keywords"]').length > 0 && this.options.jsonChildProduct['meta_data']['meta_keyword'] == null) {
                    this.options.jsonChildProduct['meta_data']['meta_keyword'] = $('head meta[name="keywords"]').attr('content');
                }
            }

            var options = this.options,
                gallery = $(options.mediaGallerySelector),
                priceBoxOptions = $(this.options.priceHolderSelector).priceBox('option').priceConfig || null;

            if (priceBoxOptions && priceBoxOptions.optionTemplate) {
                options.optionTemplate = priceBoxOptions.optionTemplate;
            }

            if (priceBoxOptions && priceBoxOptions.priceFormat) {
                options.priceFormat = priceBoxOptions.priceFormat;
            }
            options.optionTemplate = mageTemplate(options.optionTemplate);
            options.tierPriceTemplate = $(this.options.tierPriceTemplateSelector).html();

            options.settings = options.spConfig.containerId ?
                $(options.spConfig.containerId).find(options.superSelector) :
                $(options.superSelector);

            options.values = options.spConfig.defaultValues || {};
            options.parentImage = $('[data-role=base-image-container] img').attr('src');

            this.inputSimpleProduct = this.element.find(options.selectSimpleProduct);

            gallery.data('gallery') ?
                this._onGalleryLoaded(gallery) :
                gallery.on('gallery:loaded', this._onGalleryLoaded.bind(this, gallery));

            if (this.options.jsonModuleConfig.cpwd) {
                window.bssGallerySwitchStrategy = this.options.jsonModuleConfig.images;
            }
            window.bsssdcp = true;
            this._ResetDesc(this.options.jsonModuleConfig.desc);
            this._UpdateActiveTab();

        },

        /**
         * Override default options values settings with either URL query parameters or
         * initialized inputs values.
         * @private
         */
        _overrideDefaults: function () {
            var hashIndex = window.location.href.indexOf('#');

            if (hashIndex !== -1) {
                this._parseQueryParams(window.location.href.substr(hashIndex + 1));
            }

            if (this.options.spConfig.inputsInitialized) {
                this._setValuesByAttribute();
            }
        },

        /**
         * Parse query parameters from a query string and set options values based on the
         * key value pairs of the parameters.
         * @param {*} queryString - URL query string containing query parameters.
         * @private
         */
        _parseQueryParams: function (queryString) {
            var queryParams = $.parseQuery({
                query: queryString
            });

            $.each(queryParams, $.proxy(function (key, value) {
                this.options.values[key] = value;
            }, this));
        },

        /**
         * Override default options values with values based on each element's attribute
         * identifier.
         * @private
         */
        _setValuesByAttribute: function () {
            this.options.values = {};
            $.each(this.options.settings, $.proxy(function (index, element) {
                var attributeId;

                if (element.value) {
                    attributeId = element.id.replace(/[a-z]*/, '');
                    this.options.values[attributeId] = element.value;
                }
            }, this));
        },

        /**
         * Set up .on('change') events for each option element to configure the option.
         * @private
         */
        _setupChangeEvents: function () {
            $.each(this.options.settings, $.proxy(function (index, element) {
                $(element).on('change', this, this._configure);
            }, this));
            $(document).trigger('bssPriceChange');
        },

        /**
         * Iterate through the option settings and set each option's element configuration,
         * attribute identifier. Set the state based on the attribute identifier.
         * @private
         */
        _fillState: function () {
            $.each(this.options.settings, $.proxy(function (index, element) {
                var attributeId = element.id.replace(/[a-z]*/, '');

                if (attributeId && this.options.spConfig.attributes[attributeId]) {
                    element.config = this.options.spConfig.attributes[attributeId];
                    element.attributeId = attributeId;
                    this.options.state[attributeId] = false;
                }
            }, this));
        },

        /**
         * Set each option's child settings, and next/prev option setting. Fill (initialize)
         * an option's list of selections as needed or disable an option's setting.
         * @private
         */
        _setChildSettings: function () {
            var childSettings = [],
                settings = this.options.settings,
                index = settings.length,
                option;

            while (index--) {
                option = settings[index];

                if (index) {
                    option.disabled = true;
                } else {
                    this._fillSelect(option);
                }

                _.extend(option, {
                    childSettings: childSettings.slice(),
                    prevSetting: settings[index - 1],
                    nextSetting: settings[index + 1]
                });

                childSettings.push(option);
            }
        },

        /**
         * Setup for all configurable option settings. Set the value of the option and configure
         * the option, which sets its state, and initializes the option's choices, etc.
         * @private
         */
        _configureForValues: function () {
            if (this.options.values) {
                this.options.settings.each($.proxy(function (index, element) {
                    var attributeId = element.attributeId;

                    element.value = this.options.values[attributeId] || '';
                    this._configureElement(element);
                }, this));
            }
        },

        /**
         * Event handler for configuring an option.
         * @private
         * @param {Object} event - Event triggered to configure an option.
         */
        _configure: function (event) {
            event.data._configureElement(this);
        },

        /**
         * Configure an option, initializing it's state and enabling related options, which
         * populates the related option's selection and resets child option selections.
         * @private
         * @param {*} element - The element associated with a configurable option.
         */
        _configureElement: function (element) {
            this.simpleProduct = this._getSimpleProductId(element);

            if (element.value) {
                this.options.state[element.config.id] = element.value;

                if (element.nextSetting) {
                    element.nextSetting.disabled = false;
                    this._fillSelect(element.nextSetting);
                    this._resetChildren(element.nextSetting);
                } else {
                    if (!!document.documentMode) { //eslint-disable-line
                        this.inputSimpleProduct.val(element.options[element.selectedIndex].config.allowedProducts[0]);
                    } else {
                        this.inputSimpleProduct.val(element.selectedOptions[0].config.allowedProducts[0]);
                    }
                }
            } else {
                this._resetChildren(element);
            }

            this._reloadPrice();
            this._displayRegularPriceBlock(this.simpleProduct);
            if (!$('.prices-tier.items').length) {
                this._displayTierPriceBlock(this.simpleProduct);
            }
            this._displayNormalPriceLabel();
            if (!window.bsssdcp) {
                this._changeProductImage();
            }
            this._UpdateDetail(element);
        },

        /**
         * Change displayed product image according to chosen options of configurable product
         *
         * @private
         */
        _changeProductImage: function () {
            var images,
                initialImages = this.options.mediaGalleryInitial,
                galleryObject = $(this.options.mediaGallerySelector).data('gallery');

            if (!galleryObject) {
                return;
            }

            images = this.options.spConfig.images[this.simpleProduct];

            if (images) {
                images = this._sortImages(images);

                if (this.options.gallerySwitchStrategy === 'prepend') {
                    images = images.concat(initialImages);
                }

                images = $.extend(true, [], images);
                images = this._setImageIndex(images);

                galleryObject.updateData(images);

                $(this.options.mediaGallerySelector).AddFotoramaVideoEvents({
                    selectedOption: this.simpleProduct,
                    dataMergeStrategy: this.options.gallerySwitchStrategy
                });
            } else {
                galleryObject.updateData(initialImages);
                $(this.options.mediaGallerySelector).AddFotoramaVideoEvents();
            }

        },

        /**
         * Sorting images array
         *
         * @private
         */
        _sortImages: function (images) {
            return _.sortBy(images, function (image) {
                return image.position;
            });
        },

        /**
         * Set correct indexes for image set.
         *
         * @param {Array} images
         * @private
         */
        _setImageIndex: function (images) {
            var length = images.length,
                i;

            for (i = 0; length > i; i++) {
                images[i].i = i + 1;
            }

            return images;
        },

        /**
         * For a given option element, reset all of its selectable options. Clear any selected
         * index, disable the option choice, and reset the option's state if necessary.
         * @private
         * @param {*} element - The element associated with a configurable option.
         */
        _resetChildren: function (element) {
            if (element.childSettings) {
                _.each(element.childSettings, function (set) {
                    set.selectedIndex = 0;
                    set.disabled = true;
                });

                if (element.config) {
                    this.options.state[element.config.id] = false;
                }
            }
        },

        /**
         * Populates an option's selectable choices.
         * @private
         * @param {*} element - Element associated with a configurable option.
         */
        _fillSelect: function (element) {
            var attributeId = element.id.replace(/[a-z]*/, ''),
                options = this._getAttributeOptions(attributeId),
                prevConfig,
                index = 1,
                allowedProducts,
                i,
                j,
                finalPrice = parseFloat(this.options.spConfig.prices.finalPrice.amount),
                optionFinalPrice,
                optionPriceDiff,
                optionPrices = this.options.spConfig.optionPrices,
                allowedProductMinPrice;

            this._clearSelect(element);
            element.options[0] = new Option('', '');
            element.options[0].innerHTML = this.options.spConfig.chooseText;
            prevConfig = false;

            if (element.prevSetting) {
                prevConfig = element.prevSetting.options[element.prevSetting.selectedIndex];
            }

            if (options) {
                for (i = 0; i < options.length; i++) {
                    allowedProducts = [];
                    optionPriceDiff = 0;

                    /* eslint-disable max-depth */
                    if (prevConfig) {
                        for (j = 0; j < options[i].products.length; j++) {
                            // prevConfig.config can be undefined
                            if (prevConfig.config &&
                                prevConfig.config.allowedProducts &&
                                prevConfig.config.allowedProducts.indexOf(options[i].products[j]) > -1) {
                                allowedProducts.push(options[i].products[j]);
                            }
                        }
                    } else {
                        allowedProducts = options[i].products.slice(0);

                        if (typeof allowedProducts[0] !== 'undefined' &&
                            typeof optionPrices[allowedProducts[0]] !== 'undefined') {
                            allowedProductMinPrice = this._getAllowedProductWithMinPrice(allowedProducts);
                            optionFinalPrice = parseFloat(optionPrices[allowedProductMinPrice].finalPrice.amount);
                            optionPriceDiff = optionFinalPrice - finalPrice;

                            if (optionPriceDiff !== 0) {
                                // options[i].label = options[i].label + ' ' + priceUtils.formatPrice(
                                //     optionPriceDiff,
                                //     this.options.priceFormat,
                                //     true);
                            }
                        }
                    }

                    if (allowedProducts.length > 0) {
                        options[i].allowedProducts = allowedProducts;
                        element.options[index] = new Option(this._getOptionLabel(options[i]), options[i].id);

                        if (typeof options[i].price !== 'undefined') {
                            element.options[index].setAttribute('price', options[i].price);
                        }

                        element.options[index].config = options[i];
                        index++;
                    }

                    /* eslint-enable max-depth */
                }
            }
        },

        /**
         * Generate the label associated with a configurable option. This includes the option's
         * label or value and the option's price.
         * @private
         * @param {*} option - A single choice among a group of choices for a configurable option.
         * @return {String} The option label with option value and price (e.g. Black +1.99)
         */
        _getOptionLabel: function (option) {
            return option.label;
        },

        /**
         * Removes an option's selections.
         * @private
         * @param {*} element - The element associated with a configurable option.
         */
        _clearSelect: function (element) {
            var i;

            for (i = element.options.length - 1; i >= 0; i--) {
                element.remove(i);
            }
        },

        /**
         * Retrieve the attribute options associated with a specific attribute Id.
         * @private
         * @param {Number} attributeId - The id of the attribute whose configurable options are sought.
         * @return {Object} Object containing the attribute options.
         */
        _getAttributeOptions: function (attributeId) {
            if (this.options.spConfig.attributes[attributeId]) {
                return this.options.spConfig.attributes[attributeId].options;
            }
        },

        /**
         * Reload the price of the configurable product incorporating the prices of all of the
         * configurable product's option selections.
         */
        _reloadPrice: function () {
            $(this.options.priceHolderSelector).trigger('updatePrice', this._getPrices());
        },

        /**
         * Get product various prices
         * @returns {{}}
         * @private
         */
        _getPrices: function () {
            var prices = {},
                elements = _.toArray(this.options.settings),
                allowedProduct;

            _.each(elements, function (element) {
                var selected = element.options[element.selectedIndex],
                    config = selected && selected.config,
                    priceValue = {};

                if (config && config.allowedProducts.length === 1) {
                    priceValue = this._calculatePrice(config);
                } else if (element.value) {
                    allowedProduct = this._getAllowedProductWithMinPrice(config.allowedProducts);
                    priceValue = this._calculatePrice({
                        'allowedProducts': [
                            allowedProduct
                        ]
                    });
                }

                if (!_.isEmpty(priceValue)) {
                    prices.prices = priceValue;
                }
            }, this);

            return prices;
        },

        /**
         * Get product with minimum price from selected options.
         *
         * @param {Array} allowedProducts
         * @returns {String}
         * @private
         */
        _getAllowedProductWithMinPrice: function (allowedProducts) {
            var optionPrices = this.options.spConfig.optionPrices,
                product = {},
                optionMinPrice, optionFinalPrice;

            _.each(allowedProducts, function (allowedProduct) {
                optionFinalPrice = parseFloat(optionPrices[allowedProduct].finalPrice.amount);

                if (_.isEmpty(product) || optionFinalPrice < optionMinPrice) {
                    optionMinPrice = optionFinalPrice;
                    product = allowedProduct;
                }
            }, this);

            return product;
        },

        /**
         * Returns prices for configured products
         *
         * @param {*} config - Products configuration
         * @returns {*}
         * @private
         */
        _calculatePrice: function (config) {
            var displayPrices = $(this.options.priceHolderSelector).priceBox('option').prices,
                newPrices = this.options.spConfig.optionPrices[_.first(config.allowedProducts)];

            _.each(displayPrices, function (price, code) {
                if (newPrices[code]) {
                    displayPrices[code].amount = newPrices[code].amount - displayPrices[code].amount;
                }
            });

            return displayPrices;
        },

        /**
         * Returns Simple product Id
         *  depending on current selected option.
         *
         * @private
         * @param {HTMLElement} element
         * @returns {String|undefined}
         */
        _getSimpleProductId: function (element) {
            // TODO: Rewrite algorithm. It should return ID of
            //        simple product based on selected options.
            var allOptions = element.config.options,
                value = element.value,
                config;

            config = _.filter(allOptions, function (option) {
                return option.id === value;
            });
            config = _.first(config);

            if (!_.isEmpty(config) && config.allowedProducts.length === 1) {
                return _.first(config.allowedProducts);
            } else {
                return undefined;
            }

        },

        /**
         * Show or hide regular price block
         *
         * @param {*} optionId
         * @private
         */
        _displayRegularPriceBlock: function (optionId) {
            var shouldBeShown = true;

            _.each(this.options.settings, function (element) {
                if (element.value === '') {
                    shouldBeShown = false;
                }
            });

            if (shouldBeShown &&
                this.options.spConfig.optionPrices[optionId].oldPrice.amount !==
                this.options.spConfig.optionPrices[optionId].finalPrice.amount
            ) {
                $(this.options.slyOldPriceSelector).show();
            } else {
                $(this.options.slyOldPriceSelector).hide();
            }

            $(document).trigger('updateMsrpPriceBlock',
                [
                    optionId,
                    this.options.spConfig.optionPrices
                ]
            );
        },

        /**
         * Show or hide normal price label
         *
         * @private
         */
        _displayNormalPriceLabel: function () {
            var shouldBeShown = false;

            _.each(this.options.settings, function (element) {
                if (element.value === '') {
                    shouldBeShown = true;
                }
            });

            if (shouldBeShown) {
                $(this.options.normalPriceLabelSelector).show();
            } else {
                $(this.options.normalPriceLabelSelector).hide();
            }
        },

        /**
         * Callback which fired after gallery gets initialized.
         *
         * @param {HTMLElement} element - DOM element associated with gallery.
         */
        _onGalleryLoaded: function (element) {
            var galleryObject = element.data('gallery');

            this.options.mediaGalleryInitial = galleryObject.returnCurrentImages();
        },

        /**
         * Show or hide tier price block
         *
         * @param {*} optionId
         * @private
         */
        _displayTierPriceBlock: function (optionId) {
            var options, tierPriceHtml;

            if (typeof optionId != 'undefined' &&
                this.options.spConfig.optionPrices[optionId].tierPrices != [] // eslint-disable-line eqeqeq
            ) {
                options = this.options.spConfig.optionPrices[optionId];

                if (this.options.tierPriceTemplate) {
                    tierPriceHtml = mageTemplate(this.options.tierPriceTemplate, {
                        'tierPrices': options.tierPrices,
                        '$t': $t,
                        'currencyFormat': this.options.spConfig.currencyFormat,
                        'priceUtils': priceUtils
                    });
                    $(this.options.tierPriceBlockSelector).html(tierPriceHtml).show();
                }
            } else {
                $(this.options.tierPriceBlockSelector).hide();
            }
        },

        _UpdateDetail: function (element) {
            var $widget = this,
                childProductData = this.options.jsonChildProduct,
                moduleConfig = this.options.jsonModuleConfig;
            var elements = _.toArray(this.options.settings);
            $widget.url = '';
            var selectedz = element.options[element.selectedIndex];
            var config1 = selectedz && selectedz.config;
            var index = false;
            if (config1 && config1.allowedProducts.length === 1) {
                index = $widget._getSimpleProductId(element);
            }
            _.each(elements, function (element1) {
                var selected = element1.options[element1.selectedIndex],
                    config = selected && selected.config;
                var optionLabel;
                if (config) {
                    optionLabel = config.label;
                    $widget.url += '+' + element1.config.code + '-' + optionLabel;
                }
            })
            if (!index && this.simpleProduct) {
                index = this.simpleProduct;
            }
            if (childProductData['is_ajax_load'] > 0) {
                if (index) {
                    if (childProductData['child'][index] === undefined) {
                        $.ajax({
                            url: $widget.options.ajaxUrl,
                            type: 'POST',
                            data: $.param({
                                product_id: index,
                            }),
                            dataType: 'json',
                            success : function (data) {
                                $widget.options.jsonChildProduct['child'][index] = data;
                                if (data !== false) {
                                    $widget._UpdateUrl(
                                        childProductData['url'],
                                        $widget.url,
                                        moduleConfig['url'],
                                        moduleConfig['url_suffix']
                                    );
                                    $widget._UpdatePriceAjax(data['price'], false);
                                    $widget._UpdateUrl(
                                        childProductData['url'],
                                        $widget.url,
                                        moduleConfig['url'],
                                        moduleConfig['url_suffix']
                                    );
                                    $widget._UpdateDetailData(data);
                                } else {
                                    $widget._UpdatePriceAjax(childProductData['price'], true);
                                    $widget._ResetDetail();
                                }
                                $(document).trigger('contentUpdated');
                            }
                        });
                    } else if (childProductData['child'][index] !== false) {
                        $widget._UpdatePriceAjax(childProductData['child'][index]['price'], false);
                        $widget._UpdateUrl(
                            childProductData['url'],
                            $widget.url,
                            moduleConfig['url'],
                            moduleConfig['url_suffix']
                        );
                        $widget._UpdateDetailData(childProductData['child'][index]);
                    }
                } else {
                    $widget._UpdatePriceAjax(childProductData['price'], true);
                    $widget._ResetDetail();
                    return false;
                }
            } else {
                if (!childProductData['child'].hasOwnProperty(index)) {
                    $widget._ResetDetail();
                    return false;
                }
                $widget._UpdateUrl(
                    childProductData['url'],
                    $widget.url,
                    moduleConfig['url'],
                    moduleConfig['url_suffix']
                );
                $widget._UpdateDetailData(childProductData['child'][index]);
            }
        },
        _UpdateDetailData: function (data) {
            var moduleConfig = this.options.jsonModuleConfig,
                childProductData = this.options.jsonChildProduct,
                $widget = this;

            // Compatible with Product Custom Tab module
            $widget._UpdateCustomTab(data);

            $widget._UpdateSku(data['sku'], moduleConfig['sku']);

            $widget._UpdateName(data['name'], moduleConfig['name']);

            $widget._UpdateDesc(
                data['desc'],
                data['sdesc'],
                moduleConfig['desc']
            );

            $widget._UpdateAdditionalInfo(
                data['additional_info'],
                moduleConfig['additional_info']
            );

            $widget._UpdateActiveTab();

            $widget._UpdateMetaData(
                data['meta_data'],
                childProductData['meta_data'],
                moduleConfig['meta_data']
            );

            $widget._UpdateStock(
                data['stock_status'],
                data['stock_number'],
                moduleConfig['stock']
            );

            $widget._UpdateTierPrice(
                data['price']['tier_price'],
                data['price']['basePrice'],
                moduleConfig['tier_price']
            );

            $widget._UpdateChildCustomOption();

            if (this.mediaInit) {
                $widget._UpdateGallery(
                    data['image'],
                    data['video'],
                    moduleConfig['images'],
                    moduleConfig['video'],
                    true
                );
            }
        },

        /**
         * Update product child custom option
         *
         * @private
         */
        _UpdateChildCustomOption: function () {
            var $widget = this,
                productId = this.simpleProduct;
            $widget._GetCustomOption(productId);
        },

        _GetCustomOption: function(productId) {
            $('.bss-child-option').each(function () {
                $(this).addClass('bss-hidden');
                $(this).find('.product-custom-option').css({'display':'none'});
            });
            if ($('.child-option-' + productId).length) {
                $('.child-option-' + productId).removeClass('bss-hidden');
                $('.child-option-' + productId).find('.product-custom-option').removeAttr('style');
                $('.child-option-' + productId + ' .product-custom-option').change()
                return;
            }

            if (productId) {
                var ajaxUrl = this.options.spConfig.ajax_option_url,
                    customOption = this.options.sdcp_classes.customOption,
                    self = this,
                    html = '';
                $.ajax({
                    url: ajaxUrl,
                    data: {product_id: productId},
                    type: 'post',
                    dataType: 'json',
                    /** @inheritdoc */
                    beforeSend: function () {
                        $(document.body).trigger('processStart');
                    },
                    success: function (data) {
                        if (!self.options.productOptions.hasOwnProperty(productId) && data !== false) {
                            self.options.productOptions[productId] = true;
                            html = '<div class="bss-child-option child-option-' + productId + ' fieldset">' + data + '</div>';
                            $(customOption).append(html);
                        }

                        if (data && self.options.productOptions.hasOwnProperty(productId)) {
                            $('.bss-child-option.child-option-' + productId + '.product-custom-option').trigger('change');
                        }
                    },
                    complete: function() {
                        $(document).trigger('loadChildOption');
                        $(document).trigger('contentUpdated');
                        $('.field.date').trigger('contentUpdated');
                        $(document.body).trigger('processStop');
                        $('.product-custom-option').trigger('change');
                    }
                })
            }
        },
        _UpdateActiveTab: function () {
            $('.data.item.title').removeClass("active");
            $('.data.item.content').css('display', 'none');
            if ($(window.location).attr('hash') == '') {
                $('.data.item.title:not(.' +this.options.sdcp_classes.hiddenTab+ ')').first().addClass('active');
                $('.data.item.content:not(.' +this.options.sdcp_classes.hiddenTab+ ')').first().css('display', 'block');

            }
        },
        _UpdateSku: function ($sku, $config) {
            if ($config > 0) {
                $(this.options.sdcp_classes.sku).html($sku);
            }
        },
        _UpdateName: function ($name, $config) {
            if ($config > 0) {
                $(this.options.sdcp_classes.name).html($name);
            }
        },
        _UpdateDesc: function ($desc, $sdesc, $config) {
            if ($config > 0) {
                this._UpdateFullDesc($desc);
                this._UpdateShortDesc($sdesc);
            }
        },
        _UpdateFullDesc: function ($desc) {
            var html,
                classes = this.options.sdcp_classes;

            if ($desc) {
                if (!$(classes.fullDesc.label).hasClass(classes.hiddenTab)) {
                    $(classes.fullDesc.content).html($desc);
                } else {
                    $(classes.fullDesc.label).removeClass(classes.hiddenTab);
                    $(classes.fullDesc.blockContent).removeClass(classes.hiddenTab);
                    $(classes.fullDesc.content).html($desc);
                }
            } else {
                $(classes.fullDesc.label).addClass(classes.hiddenTab);
                $(classes.fullDesc.blockContent).addClass(classes.hiddenTab);
            }
        },
        _UpdateShortDesc: function ($sdesc) {
            var html;
            if ($sdesc) {
                if ($(this.options.sdcp_classes.shortDesc).find('.value').length) {
                    $(this.options.sdcp_classes.shortDesc).find('.value').html($sdesc);
                    $(this.options.sdcp_classes.shortDesc).fadeIn();
                } else {
                    html = '<div class="product attribute overview">'
                        + '<div class="value" itemprop="description">'
                        + $sdesc
                        + '</div></div>';
                    $(this.options.selectorProduct).append(html);
                }
            } else {
                $(this.options.sdcp_classes.shortDesc).fadeOut();
            }
        },
        _UpdateStock: function ($status, $number, $config) {
            if ($config > 0) {
                var stock_status = '';
                if ($status > 0) {
                    stock_status = $t('IN STOCK');
                    $(this.options.sdcp_classes.addtocart_button).removeAttr('disabled');
                } else {
                    stock_status = $t('OUT OF STOCK');
                    $(this.options.sdcp_classes.addtocart_button).attr('disabled', 'disabled');
                }
                stock_status += " - " + Number($number);
                $(this.options.sdcp_classes.stock).html(stock_status);
            }
        },
        _UpdateIncrement: function ($increment, $name, $config) {
            $(this.options.sdcp_classes.increment).remove();
            if ($config > 0 && $increment > 0) {
                var html = '<div class="product pricing">';
                html += $t('%1 is available to buy in increments of %2').replace('%1', $name).replace('%2', $increment);
                html += '</div>';
                $(this.options.selectorProduct).append(html);
            }
        },
        _UpdateMinQty: function ($value, $config) {
            if ($config > 0) {
                if ($value > 0) {
                    $(this.options.sdcp_classes.qty_box).val($value);
                    $(this.options.sdcp_classes.qty_box).trigger('change');
                } else {
                    $(this.options.sdcp_classes.qty_box).val(1);
                    $(this.options.sdcp_classes.qty_box).trigger('change');
                }
            }
        },
        _UpdateTierPrice: function ($priceData, $basePrice, $moduleConfig) {
            if ($moduleConfig > 0) {
                var $widget = this,
                    percent,
                    html = '',
                    htmlTierPrice = '',
                    have_tier_price = false,
                    htmlTierPrice4 = '<span class="percent tier-%4">&nbsp;%5</span>%</strong>',
                    htmlTierPrice5 = '<span class="price-container price-tier_price tax weee"><span data-price-amount="%2" data-price-type="" class="price-wrapper "><span class="price">%3</span></span></span>';
                $(this.options.sdcp_classes.tier_price).remove();
                html = '<ul class="prices-tier items">';
                $.each($priceData, function (key, vl) {
                    percent = Math.round((1 - Number(vl['base'])/Number($basePrice)) * 100);
                    if (percent == 0) {
                        percent = ((1 - Number(vl['base'])/Number($basePrice)) * 100).toFixed(2);
                    }
                    have_tier_price = true;
                    htmlTierPrice = $t('Buy %1 for ').replace('%1', Number(vl['qty']));
                    htmlTierPrice += htmlTierPrice5.replace('%2', Number(vl['value'])).replace('%3', $widget._getFormattedPrice(Number(vl['value'])));
                    htmlTierPrice += $t(' each and ');
                    htmlTierPrice += '<strong class="benefit">';
                    htmlTierPrice += $t('save');
                    htmlTierPrice += htmlTierPrice4.replace('%4', key).replace('%5', percent);
                    html += '<li class="item">';
                    html += htmlTierPrice;
                    html += '</li>';
                });
                html += '</ul>';
                if (have_tier_price) {
                    $('.product-info-price').after(html);
                }
            }
        },
        _UpdateGallery: function (images, video, $config, $configVideo, trigger) {
            if ($config === IMAGE_CONFIG_DISABLED) {
                return;
            }
            if (images.length === 0) {
                return this._ResetGallery($config, $configVideo);
            }
            var $widget = this,
                justAnImage = images[0],
                updateImg,
                $this = $widget.element,
                imagesToUpdate,
                galleryObject = $(this.options.mediaGallerySelector).data('gallery');;
            if ($config === IMAGE_CONFIG_PREPEND) {
                if ($widget.options.onlyMainImg) {
                    $.each(images, function ($id, $vl) {
                        if ($vl.isMain) {
                            imagesToUpdate = $widget.options.jsonChildProduct['image'];
                            imagesToUpdate[0] = $vl;
                            return true;
                        }
                    });
                    images = imagesToUpdate;
                } else {
                    images = images.concat($widget.options.jsonChildProduct['image']);
                }
            }
            images = $.extend(true, [], images);
            images = this._setImageIndex(images);
            galleryObject.updateData(images);

            if (_.findKey(images, {type: 'video'}) !== undefined) {
                $widget._UpdateVideo(
                    video,
                    $configVideo,
                    $config,
                    trigger
                );
            } else {
                $widget._ResetVideo(
                    $configVideo,
                    $config
                );
            }
        },
        _UpdateVideo: function (video, $configVideo, $configImage, trigger) {
            if ($configImage === IMAGE_CONFIG_DISABLED) {
                return;
            }
            var magento21x = this.options.magento21x,
                videoHolder = $(this.options.mediaGallerySelector),
                activeFrame,
                videoToUpdate;
            if (magento21x) {
                if (video.length == 0) {
                    activeFrame = 999;
                } else {
                    activeFrame = 1;
                }
                if ($configImage === IMAGE_CONFIG_PREPEND) {
                    if (this.options.onlyMainImg) {
                        var widget = this;
                        $.each(video, function ($id, $vl) {
                            if ($vl.isMain) {
                                videoToUpdate = widget.options.jsonChildProduct['video'];
                                videoToUpdate[0] = $vl;
                                return true;
                            }
                        });
                        video = videoToUpdate;
                    } else {
                        video = video.concat(this.options.jsonChildProduct['video']);
                    }
                }
                videoHolder.AddFotoramaVideoEvents({
                    videoData: video,
                    videoSettings: $configVideo,
                    optionsVideoData: []
                });
                videoHolder.find('.fotorama-item').data('fotorama').activeFrame.i = activeFrame;
                if (trigger) {
                    $(this.options.mediaGallerySelector).trigger('gallery:loaded');
                }
            } else {
                $(this.options.mediaGallerySelector).AddFotoramaVideoEvents({
                    selectedOption: this.simpleProduct,
                    dataMergeStrategy: $configImage
                });
            }
            videoHolder.data('gallery').first();
        },
        _UpdateAdditionalInfo: function ($info, $config) {
            var html = '',
                classes = this.options.sdcp_classes,
                index,
                name,
                reviewCount;
            if ($config > 0) {
                if (Object.keys($info) != '') {
                    $.each($info, function ($id, $vl) {
                        html += '<tr>'
                            + '<th class="col label" scope="row">' + $vl['label'] + '</th>'
                            + '<td class="col data" data-th="' + $vl['label'] + '">' + $vl['value'] + '</td>'
                            + '</tr>';
                    });
                    if (!$(classes.additionalInfo.label).hasClass(classes.hiddenTab)) {
                        $(classes.additionalInfo.content).find('tbody').html(html);
                    } else {
                        $(classes.additionalInfo.label).removeClass(classes.hiddenTab);
                        $(classes.additionalInfo.content).removeClass(classes.hiddenTab).find('tbody').html(html);
                    }
                } else {
                    $(classes.additionalInfo.label).addClass(classes.hiddenTab);
                    $(classes.additionalInfo.content).addClass(classes.hiddenTab);
                }

                index = this.simpleProduct;

                if (index && this.options.jsonChildProduct['child'][index] !== undefined) {
                    name = this.options.jsonChildProduct['child'][index]['name'];
                    reviewCount = this.options.jsonChildProduct['child'][index]['review_count'];
                    if (this.options.jsonChildProduct['child'][index]['review_count'] > 0) {
                        this._UpdateReview(index);
                    } else {
                        $('#product-review-container').html('').trigger('contentUpdated');
                    }
                } else {
                    index = this.options.jsonChildProduct.entity;
                    name = this.options.jsonChildProduct.name;
                    reviewCount = this.options.jsonChildProduct.review_count;
                    this._UpdateReview(index);
                }

                this._UpdatePostReviewUrl(index);
                this._UpdateReviewName(name);
                this._UpdateReviewCount(reviewCount);
            }
        },
        _UpdateReview: function(index) {
            var self = this,
                url = urlBuilder.build('/bss_sdcp/ajax_product/listAjax/id/' + index);

            $.ajax({
                url: url,
                cache: true,
                loaderContext: $('.product.data.items')
            }).done(function (data) {
                $('#product-review-container').html(data).trigger('contentUpdated');
                if ($('#tab-label-reviews').hasClass('active')) {
                    $('#product-review-container').parent().show();
                }
            });
        },

        _UpdateReviewCount: function(count) {
            if (count == 0) {
                $('#tab-label-reviews-title').find('.counter').remove();
            } else {
                if ($('#tab-label-reviews-title').find('.counter').length) {
                    $('#tab-label-reviews-title').find('.counter').html(count);
                } else {
                    $('#tab-label-reviews-title').append('<span class="counter">' + count + '</span>');
                }
            }
        },

        _UpdatePostReviewUrl: function(index) {
            var url = urlBuilder.build('/bss_sdcp/ajax_product/post/id/' + index);
            $('#review-form').attr('action', url);
        },

        _UpdateReviewName: function(name) {
            $('.review-add .review-legend strong').text(name);
        },

        _UpdateMetaData: function ($metaData, $parentMetaData, $config) {
            if ($config > 0) {
                if ($metaData['meta_description'] != null) {
                    $('head meta[name="description"]').attr('content', $metaData['meta_description']);
                } else {
                    $('head meta[name="description"]').attr('content', $parentMetaData['meta_description']);
                }
                if ($metaData['meta_keyword'] != null) {
                    if ($('head meta[name="keywords"]').length > 0) {
                        $('head meta[name="keywords"]').attr('content', $metaData['meta_keyword']);
                    } else {
                        $('head meta[name="description"]').after(
                            '<meta name="keywords" content="' + $metaData['meta_keyword'] + '" />'
                        );
                    }
                } else {
                    if ($parentMetaData['meta_keyword'] != null) {
                        if ($('head meta[name="keywords"]').length > 0) {
                            $('head meta[name="keywords"]').attr('content', $parentMetaData['meta_keyword']);
                        } else {
                            $('head meta[name="description"]').after(
                                '<meta name="keywords" content="' + $parentMetaData['meta_keyword'] + '" />'
                            );
                        }
                    } else {
                        $('head meta[name="keywords"]').remove();
                    }
                }
                if ($metaData['meta_title'] != null) {
                    document.title = $metaData['meta_title'];
                } else {
                    document.title = $parentMetaData['meta_title'];
                }
            }
        },
        _UpdateUrl: function ($parentUrl, $customUrl, $config, $suffix) {
            if ($config > 0 && $('.checkout-cart-configure').length == 0) {
                $customUrl = $customUrl.replace(/[`!@#$%^&*()|\;:'",.<>\{\}\[\]\\\/]/g,'');
                $customUrl = $.trim($customUrl);
                while ($customUrl.indexOf(' ') >= 0) {
                    $customUrl = $customUrl.replace(" ", "~");
                }
                var suffixPos = $parentUrl.indexOf($suffix);
                if (suffixPos > 0) {
                    $parentUrl = $parentUrl.substring(0, suffixPos);
                }
                var url = $parentUrl + $customUrl;
                window.history.replaceState('SDCP', 'SCDP', url);
            }
        },
        _UpdateSelected: function ($options, $widget) {
            var config = $options.jsonModuleConfig,
                data = $options.jsonChildProduct,
                customUrl = this.options.checkLocation,
                selectingAttr = [],
                attr,
                selectedAttr = customUrl.split('+'),
                rootUrl = customUrl.split('+'),
                flag = false,
                atttrbutes = this.options.spConfig.attributes;
            if (data['is_ajax_load'] > 0) {
                $widget.mediaInit = true;
            }
            rootUrl = rootUrl.slice(0,1);
            selectedAttr.shift();
            if (!this.options.jsonModuleConfig.cpwd) {
                if (config['url'] > 0 && selectedAttr.length > 0) {
                    // this.options.jsonChildProduct.url = rootUrl[0];
                    flag = this._preselectByUrl(config, selectedAttr, atttrbutes);
                } else {
                    this.options.jsonChildProduct.url = customUrl;
                    flag = this._preselectByConfig(config, data);
                }
            }
            this.mediaInit = true;
        },
        _preselectByUrl: function (config, selectedAttr, atttrbutes) {
            var $code, $value, $arrayAttribute = [];
            $.each(atttrbutes, function ($index, $v2) {
                // $checkAttribute = $v2.code.replace('_', '');
                $arrayAttribute[$v2.code] = [];
                $.each($v2.options, function ($index3, $v3) {
                    var data = [];
                    var v3label = $v3.label.replace(/ /g, '').replace(/[~`!@#$%^&*()|\;:'",.<>\{\}\[\]\\\/]/g,'');
                    data['parent'] = $index;
                    data['children'] = $v3.id;

                    $arrayAttribute[$v2.code][v3label] = data;
                });

            });
            $.each(selectedAttr, function ($index, $vl) {
                if (typeof $vl === 'string') {
                    $code = $vl.substring(0, $vl.indexOf('-'));
                    $value = $vl.substring($code.length + 1);
                    $value = $value.replace(/~/g,'');
                    $value = decodeURIComponent($value);
                    if (typeof($arrayAttribute[$code][$value]) != "undefined" && $arrayAttribute[$code][$value] !== null) {
                        var datas = $arrayAttribute[$code][$value];

                        try {
                            $('.configurable #attribute'
                                + datas.parent
                                + '').val(datas.children).trigger('change');
                            return true;
                        } catch (e) {
                            console.log($.mage.__('Error when get product from urls'));
                        }
                    }
                }
            });
            return true;
        },
        _preselectByConfig: function (config, data) {
            if (config['preselect'] > 0 && data['preselect']['enabled'] > 0) {
                $.each(data['preselect']['data'], function ($index, $vl) {
                    try {
                        $('.configurable #attribute'
                            + $index
                            + '').val($vl).trigger('change');
                    } catch (e) {
                        console.log($.mage.__('Error when applied preselect product'));
                    }
                });
                return true;
            }
            return false;
        },
        _ValidateQty: function ($widget) {
            var keymap, index,
                data = $widget.options.jsonChildProduct,
                config = $widget.options.jsonModuleConfig,
                state;
            $('input.input-text.qty').change(function () {
                index = this.simpleProduct;
                if (data['child'].hasOwnProperty(index) && data['child'][index]['stock_status'] > 0) {
                    state = data['child'][index]['stock_status'] > 0;
                    if (!state) {
                        $($widget.options.sdcp_classes.addtocart_button).attr('disabled', 'disabled');
                    } else {
                        $($widget.options.sdcp_classes.addtocart_button).removeAttr('disabled');
                    }
                }
            });
        },
        _ResetDetail: function () {
            var moduleConfig = this.options.jsonModuleConfig;
            this._ResetSku(moduleConfig['sku']);
            this._ResetName(moduleConfig['name']);
            this._ResetDesc(moduleConfig['desc']);
            this._ResetStock(moduleConfig['stock']);
            this._ResetTierPrice(moduleConfig['tier_price']);
            this._ResetUrl(moduleConfig['url']);
            this._ResetIncrement(moduleConfig['increment']);
            this._UpdateAdditionalInfo(
                this.options.jsonChildProduct['additional_info'],
                moduleConfig['additional_info']
            );
            this._UpdateActiveTab();
            this._ResetMetaData(moduleConfig['meta_data']);
            if (this.mediaInit) {
                this._ResetGallery(moduleConfig['images'], moduleConfig['video']);
            }
            this._UpdateChildCustomOption();
        },
        _ResetSku: function ($config) {
            if ($config > 0) {
                $(this.options.sdcp_classes.sku).html(this.options.jsonChildProduct['sku']);
            }
        },
        _ResetName: function ($config) {
            if ($config > 0) {
                $(this.options.sdcp_classes.name).html(this.options.jsonChildProduct['name']);
            }
        },
        _ResetDesc: function ($config) {
            if ($config > 0) {
                if (this.options.jsonChildProduct['desc']) {
                    $(this.options.sdcp_classes.fullDesc.label).removeClass(this.options.sdcp_classes.hiddenTab);
                    $(this.options.sdcp_classes.fullDesc.blockContent).removeClass(this.options.sdcp_classes.hiddenTab);
                    $(this.options.sdcp_classes.fullDesc.content).html(this.options.jsonChildProduct['desc']);
                } else {
                    $(this.options.sdcp_classes.fullDesc.label).addClass(this.options.sdcp_classes.hiddenTab);
                    $(this.options.sdcp_classes.fullDesc.blockContent).addClass(this.options.sdcp_classes.hiddenTab);
                }
                $(this.options.sdcp_classes.shortDesc).find('.value').html(this.options.jsonChildProduct['sdesc']);
            }
        },
        _ResetStock: function ($config) {
            if ($config > 0) {
                var stock_status = '';
                if (this.options.jsonChildProduct['stock_status'] > 0) {
                    stock_status = $t('IN STOCK');
                    $(this.options.sdcp_classes.addtocart_button).removeAttr('disabled');
                } else {
                    stock_status = $t('OUT OF STOCK');
                    $(this.options.sdcp_classes.addtocart_button).attr('disabled', 'disabled');
                }
                $(this.options.sdcp_classes.stock).html(stock_status);
            }
        },
        _ResetTierPrice: function ($config) {
            if ($config > 0) {
                $(this.options.sdcp_classes.tier_price).remove();
            }
        },
        _ResetIncrement: function ($config) {
            if ($config > 0) {
                $(this.options.sdcp_classes.increment).remove();
            }
        },
        _ResetMetaData: function ($config) {
            var $metaData = this.options.jsonChildProduct['meta_data']
            if ($config > 0) {
                $('head meta[name="description"]').attr('content', $metaData['meta_description']);
                $('head meta[name="keywords"]').attr('content', $metaData['meta_keyword']);
                document.title = $metaData['meta_title'];
            }
        },
        _ResetGallery: function ($configImage, $configVideo) {

            var images = this.options.jsonChildProduct['image'],
                gallery = $(this.options.mediaGallerySelector).data('gallery');
            if ($configImage === IMAGE_CONFIG_DISABLED || !gallery) {
                return;
            }
            if (!this.options.magento21x) {
                images = this._setImageIndex(images);
            }
            gallery.updateData(images);
            this._ResetVideo($configVideo, $configImage);
        },
        _ResetVideo: function ($configVideo, $configImage) {
            if ($configImage === IMAGE_CONFIG_DISABLED) {
                return;
            }
            var magento21x = this.options.magento21x,
                videoHolder = $(this.options.mediaGallerySelector),
                activeFrame,
                video = this.options.jsonChildProduct['video'];
            if (magento21x) {
                if (video.length == 0) {
                    activeFrame = 999;
                } else {
                    activeFrame = 1;
                }

                videoHolder.AddFotoramaVideoEvents({
                    videoData: video,
                    videoSettings: $configVideo,
                    optionsVideoData: []
                });
                videoHolder.find('.fotorama-item').data('fotorama').activeFrame.i = activeFrame;
                videoHolder.trigger('gallery:loaded');
            } else {
                videoHolder.AddFotoramaVideoEvents();
            }
            videoHolder.data('gallery').first();
        },
        _ResetUrl: function ($config) {
            if ($config > 0) {
                window.history.replaceState(null, null, this.options.jsonChildProduct['url']);
            }
        },

        /**
         * Update total price
         *
         * @private
         */
        _UpdatePrice: function () {
            var $widget = this,
                index = this.simpleProduct,
                $product = $widget.element.parents($widget.options.selectorProduct),
                $productPrice = $product.find(this.options.selectorProductPrice),
                options = _.object(_.keys($widget.optionsMap), {}),
                childData = $widget.options.jsonChildProduct['child'],
                result = {
                    oldPrice: {amount: 0},
                    basePrice: {amount: 0},
                    finalPrice: {amount: 0}
                };

            if ($widget.options.jsonChildProduct['is_ajax_load'] > 0) {
                return;
            }
            var $taxRate,
                $sameRateAsStore;

            if (childData.hasOwnProperty(index)) {
                $(this.options.normalPriceLabelSelector).hide();
                result.oldPrice.amount = Number(childData[index]['price']['oldPrice']);
                result.basePrice.amount = Number(childData[index]['price']['basePrice']);
                result.finalPrice.amount = Number(childData[index]['price']['finalPrice']);
            } else {
                $(this.options.normalPriceLabelSelector).show();
                result.oldPrice.amount = Number($widget.options.jsonChildProduct['price']['finalPrice']);
                result.basePrice.amount = Number($widget.options.jsonChildProduct['price']['finalPrice']);
                result.finalPrice.amount = Number($widget.options.jsonChildProduct['price']['finalPrice']);
            }
            window.child_product_price = true;
            $productPrice.trigger(
                'updatePrice',
                {
                    'prices': $widget._getPrices(result, $productPrice.priceBox('option').prices)
                }
            );
            $('.product-custom-option').each(function(){
                if (!$(this).parents('.bss-child-option').length) {
                    $(this).change()
                }
            });
            if (result.oldPrice.amount !== result.finalPrice.amount) {
                $(this.options.slyOldPriceSelector).show();
            } else {
                $(this.options.slyOldPriceSelector).hide();
            }
        },

        _UpdatePriceAjax: function ($prices, showLabel) {
            var $widget = this,
                $product = $widget.element.parents($widget.options.selectorProduct),
                $productPrice = $product.find(this.options.selectorProductPrice),
                options = _.object(_.keys($widget.optionsMap), {}),
                result = {
                    oldPrice: {amount: 0},
                    basePrice: {amount: 0},
                    finalPrice: {amount: 0}
                };

            if (showLabel) {
                $($widget.options.normalPriceLabelSelector).show();
            } else {
                $($widget.options.normalPriceLabelSelector).hide();
            }
            result.oldPrice.amount = Number($prices['oldPrice']);
            result.basePrice.amount = Number($prices['basePrice']);
            result.finalPrice.amount = Number($prices['finalPrice']);
            window.child_product_price = true;
            $productPrice.trigger(
                'updatePrice',
                {
                    'prices': $widget._getPrices(result, $productPrice.priceBox('option').prices)
                }
            );
            $('.product-custom-option').each(function(){
                if (!$(this).parents('.bss-child-option').length) {
                    $(this).change()
                }
            });
            if (result.oldPrice.amount !== result.finalPrice.amount) {
                $(this.options.slyOldPriceSelector).show();
            } else {
                $(this.options.slyOldPriceSelector).hide();
            }
        },
        _UpdateCustomTab: function(data) {
            // Toggle tab
            // Compatible with product custom tabs module
            var productId = data.entity;
            if (undefined !== productId) {
                $('div.data.item.title').each(function (idx, elem) {
                    var triggerIds = $(elem).attr('data-trigger');
                    if (undefined !== triggerIds) {
                        if (triggerIds.split(",").indexOf(productId) !== -1) {
                            if (!$(elem).is(':visible')) {
                                $(elem).removeClass('bss-tab-hidden').show();
                            }
                        } else {
                            if ($(elem).hasClass('active')) {
                                $(elem).removeClass('active');
                                $(elem).siblings('div.data.item.title').first().addClass('active');
                                $(elem).siblings('div.data.item.content').css({'display': 'none'});
                                $(elem).siblings('div.data.item.content').first().css({'display': 'block'});
                            }
                            $(elem).hide();
                        }
                    }
                });
            }
            // End
        },

        _getFormattedPrice: function (price) {
            return priceUtils.formatPrice(price, this.options.fomatPrice);
        }
    });

    return $.mage.configurable;
});
