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
    'Magento_Catalog/js/price-utils',
    'mage/translate',
    'Magento_Ui/js/block-loader',
    'mage/url',
    'Magento_Catalog/js/validate-product',
    'priceOptions',
    'priceOptionFile',
    'jquery/ui',
    'jquery/jquery.parsequery',
    'Magento_Swatches/js/swatch-renderer'
], function ($, _, priceUtils, $t, blockLoader, urlBuilder) {
    'use strict';

    const IMAGE_CONFIG_REPLACE = 'replace';
    const IMAGE_CONFIG_PREPEND = 'prepend';
    const IMAGE_CONFIG_DISABLED = 'disabled';

    $.widget('bss.Sdcp', $.mage.SwatchRenderer, {
        options: {
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
            productOptions: {},

            cpwd: {
                table: '.bss-ptd-table',
                row: '.bss-table-row'
            },
            customOptionChild: false
        },
        mediaInit: false,

        _init: function() {
            this._super();
            if (this.options.jsonModuleConfig.cpwd) {
                window.bssGallerySwitchStrategy = this.options.jsonModuleConfig.images;
                this._EventListener();
                this._RenderControls();
            }
        },

        _RenderControls: function () {
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

            if (!this.options.jsonModuleConfig.cpwd) {
                this._super();
            }

            this._ResetDesc(this.options.jsonModuleConfig.desc);
            this._UpdateActiveTab();
            this._UpdateSelected(this.options, this);
        },
        _EventListener: function () {
            var $widget = this,
                options = this.options.classes;

            this._super();
            this._ValidateQty(this);

            $(this.options.cpwd.table).on('click', '.' + options.optionClass, function () {
                if ($(this).hasClass('bss-table-row-attr')) {
                    return $widget._OnClick($(this), $widget);
                }
            });
        },

        _OnClick: function ($this, $widget) {
            var $parent = $this.parents('.' + $widget.options.classes.attributeClass),
                attributeId = $widget.getDataofAttr($parent, 'attribute-id');

            if (this.options.jsonModuleConfig.cpwd) {
                $parent = $this.parents($widget.options.cpwd.table);
                attributeId = $widget.getDataofAttr($this, 'attribute-id');
            }

            var $label = $parent.find('.' + $widget.options.classes.attributeSelectedOptionLabelClass),
                $input = $parent.find('.' + $widget.options.classes.attributeInput);

            if ($widget.inProductList) {
                $input = $widget.productForm.find(
                    '.' + $widget.options.classes.attributeInput + '[name="super_attribute[' + attributeId + ']"]'
                );
            }

            if ($this.hasClass('disabled')) {
                return;
            }

            $input.val(this.getDataofAttr($this, 'option-id'));

            if (!this.options.jsonModuleConfig.cpwd) {
                if ($this.hasClass('selected')) {
                    $parent.removeAttr('data-option-selected option-selected').find('.selected').removeClass('selected');
                    $input.val('');
                    $label.text('');
                    //bss_commerce
                    $widget._ResetDetail();
                } else {
                    var optionId = $widget.getDataofAttr($this, 'option-id');
                    $parent.attr({'data-option-selected' : optionId, 'option-selected' : optionId}).find('.selected').removeClass('selected');
                    $label.text($widget.getDataofAttr($this, 'option-label'));
                    $input.val(optionId);
                    $this.addClass('selected');
                }
                if ($widget.element.parents($widget.options.selectorProduct)
                    .find(this.options.selectorProductPrice).is(':data(mage-priceBox)')
                ) {
                    $widget._UpdatePrice();
                }
                $widget._Rebuild();
            } else {
                $input.val($widget.getDataofAttr($this, 'option-id'));
            }

            $widget._UpdateDetail($this);
            $input.trigger('change');
            $(document).trigger('bssPriceChange');
        },

        _OnChange: function ($this, $widget) {
            var $parent = $this.parents('.' + $widget.options.classes.attributeClass),
                attributeId = $widget.getDataofAttr($parent, 'attribute-id'),
                $input = $widget.element.closest('form').find(
                    '.' + $widget.options.classes.attributeInput + '[name="super_attribute[' + attributeId + ']"]'
                );

            if ($widget.inProductList) {
                $input = $widget.productForm.find(
                    '.' + $widget.options.classes.attributeInput + '[name="super_attribute[' + attributeId + ']"]'
                );
            }

            // on 2.4 , attirbute change to data-attribute
            if ($this.val() > 0) {
                $parent.attr({'data-option-selected': $this.val(), 'option-selected': $this.val()});
                $input.val($this.val());
            } else {
                $parent.removeAttr('data-option-selected option-selected');
                $input.val('');
            }

            if (!this.options.jsonModuleConfig.cpwd) {
                $widget._Rebuild();
            }

            //bss
            $widget._UpdatePrice();
            $widget._UpdateDetail();
            $input.trigger('change');
            $(document).trigger('bssPriceChange');
        },

        findSwatchIndex: function ($widget) {
            var options = {},
                jsonData = $widget.options.jsonConfig,
                attributes_count = 0,
                selectedElement;
            $widget.url = '';
            if ($widget.options.jsonModuleConfig.cpwd) {
                selectedElement = $widget.element.parent();
            } else {
                selectedElement = $widget.element;
            }
            selectedElement.find('.' + $widget.options.classes.attributeClass + '[option-selected]').each(function () {
                var attributeId = $widget.getDataofAttr($(this), 'attribute-id');
                options[attributeId] = $widget.getDataofAttr($(this), 'option-selected');
                attributes_count ++;
                var optionLabel;
                $(this).find('.swatch-option.selected, .swatch-select option:selected').each(function () {
                    if ($(this).hasClass('swatch-option')) {
                        optionLabel = $widget.getDataofAttr($(this), 'option-label');
                    } else {
                        optionLabel = $(this).text();
                    }
                });

                if (!optionLabel && $widget.options.jsonModuleConfig.cpwd) {
                    optionLabel = $widget.getDataofAttr($(this), 'option-label');
                }
                $widget.url += '+' + $widget.getDataofAttr($(this), 'attribute-code') + '-' + optionLabel;
            });

            if ($('#bss-ptd-table').length) {
                $('.bss-ptd-table').find('.swatch-attribute.selected').each(function () {
                    var attributeId = $widget.getDataofAttr($(this), 'attribute-id');
                    if (!options.hasOwnProperty(attributeId)) {
                        options[attributeId] = $widget.getDataofAttr($(this), 'option-id');
                        attributes_count ++;
                    }
                });
            }

            if (_.isEmpty(options)) {
                return false;
            }
            if (this.options.customOptionChild) {
                if (_.size(jsonData.attributes) == _.size(options)) {
                    return _.findKey(jsonData.index, options);
                } else {
                    return false;
                }
            }
            return _.findKey(jsonData.index, options);
        },

        _UpdateDetail: function ($this) {
            var $widget = this,
                index = $widget.findSwatchIndex($widget),
                childProductData = this.options.jsonChildProduct,
                moduleConfig = this.options.jsonModuleConfig,
                keymap,
                url = '',
                super_attribute = {};
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
                            showLoader: true,
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
                productId;

            productId = this.findSwatchIndex($widget);
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
            this.options.customOptionChild = true;
            var productId = this.findSwatchIndex(this);
            this.options.customOptionChild = false;
            if (productId) {
                var ajaxUrl = this.options.jsonConfig.ajax_option_url,
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
                gallery = $($widget.options.mediaGallerySelector).data('gallery');
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
            imagesToUpdate = images.length ? $widget._setImageType($.extend(true, [], images)) : [];
            if (!$widget.options.magento21x) {
                images = $widget._setImageIndex(images);
            }
            gallery.updateData(images);

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
                    selectedOption: this.findSwatchIndex(this),
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

                index = this.findSwatchIndex(this);

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
            } else {
                $('#product-review-container').data('additional', $config);
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
                customUrl = $(location).attr('href'),
                selectingAttr = [],
                attr,
                selectedAttr = customUrl.split('+'),
                rootUrl = customUrl.split('+'),
                flag = false;
            if (data['is_ajax_load'] > 0) {
                $widget.mediaInit = true;
            }
            rootUrl = rootUrl.slice(0,1);
            selectedAttr.shift();
            if (!this.options.jsonModuleConfig.cpwd) {
                if (config['url'] > 0 && selectedAttr.length > 0) {
                    // this.options.jsonChildProduct.url = rootUrl[0];
                    flag = this._preselectByUrl(config, selectedAttr);
                } else {
                    this.options.jsonChildProduct.url = customUrl;
                    flag = this._preselectByConfig(config, data);
                }
            }
            this.mediaInit = true;
        },
        _preselectByUrl: function (config, selectedAttr) {
            var $code, $value;
            $.each(selectedAttr, function ($index, $vl) {
                if (typeof $vl === 'string') {
                    $code = $vl.substring(0, $vl.indexOf('-'));
                    $value = $vl.substring($code.length + 1);
                    $value = $value.replace(/~/g,'');
                    $value = decodeURIComponent($value);
                    try {
                        if ($('.swatch-attribute[attribute-code="'
                            + $code
                            + '"] .swatch-attribute-options').children().is('div')) {
                            $('.swatch-attribute[attribute-code="' + $code + '"] .swatch-attribute-options .swatch-option').each(function () {
                                var optionLable = $(this).attr('option-label').replace(/[~`!@#$%^&*()|\;:'",.<>\{\}\[\]\\\/]/g,'').replace(/\s/g,'');
                                optionLable = $.trim(optionLable);
                                if (optionLable == $value) {
                                    $(this).trigger('click');
                                    return false;
                                }
                            });
                        } else {
                            $.each($('.swatch-attribute[attribute-code="'
                                + $code
                                + '"] .swatch-attribute-options select option'), function ($index2, $vl2) {
                                var $vl2Txt = $vl2.text.replace(/[`!@#$%^&*()|\;:'",.<>\{\}\[\]\\\/]/g,'');
                                $vl2Txt = $vl2Txt.replace(/~/g,'');
                                $vl2Txt = $vl2Txt.replace(/ /g,'');
                                if ($vl2Txt === $value) {
                                    $('.swatch-attribute[attribute-code="'
                                        + $code
                                        + '"] .swatch-attribute-options select').val($vl2.value).trigger('change');
                                    return true;
                                }
                            });
                        }
                        // on magento 2.4 attribute change to data-attribute
                        if ($('.swatch-attribute[data-attribute-code="'
                            + $code
                            + '"] .swatch-attribute-options').children().is('div')) {
                            $('.swatch-attribute[data-attribute-code="' + $code + '"] .swatch-attribute-options .swatch-option').each(function () {
                                var optionLable = $(this).attr('data-option-label').replace(/[~`!@#$%^&*()|\;:'",.<>\{\}\[\]\\\/]/g,'').replace(/\s/g,'');
                                optionLable = $.trim(optionLable);
                                if (optionLable == $value) {
                                    $(this).trigger('click');
                                    return false;
                                }
                            });
                        } else {
                            $.each($('.swatch-attribute[data-attribute-code="'
                                + $code
                                + '"] .swatch-attribute-options select option'), function ($index2, $vl2) {
                                var $vl2Txt = $vl2.text.replace(/[`!@#$%^&*()|\;:'",.<>\{\}\[\]\\\/]/g,'');
                                $vl2Txt = $vl2Txt.replace(/~/g,'');
                                $vl2Txt = $vl2Txt.replace(/ /g,'');
                                if ($vl2Txt === $value) {
                                    $('.swatch-attribute[data-attribute-code="'
                                        + $code
                                        + '"] .swatch-attribute-options select').val($vl2.value).trigger('change');
                                    return true;
                                }
                            });
                        }
                    } catch (e) {
                        console.log($.mage.__('Error when get product from urls'));
                    }
                }
            });
            return true;
        },
        _preselectByConfig: function (config, data) {
            var _this = this,
                defaultMagentoVals = this.options.jsonConfig.defaultValues,
                defaultVal = {};
            if (config['preselect'] > 0 && data['preselect']['enabled'] > 0) {
                defaultVal = data['preselect']['data'];
            }
            if (undefined !== defaultMagentoVals) {
                defaultVal = defaultMagentoVals;
            }
            $.each(defaultVal, function ($index, $vl) {
                try {
                    if ($('.swatch-attribute[attribute-id='
                        + $index
                        + '] .swatch-attribute-options').children().is('div')) {
                        $('.swatch-attribute[attribute-id='
                            + $index
                            + '] .swatch-attribute-options [option-id='
                            + $vl
                            + ']').trigger('click');
                    } else {
                        $('.swatch-attribute[attribute-id='
                            + $index
                            + '] .swatch-attribute-options select').val($vl).trigger('change');
                    }
                    // on magento 2.4 attribute change to data-attribute
                    if ($('.swatch-attribute[data-attribute-id='
                        + $index
                        + '] .swatch-attribute-options').children().is('div')) {
                        $('.swatch-attribute[data-attribute-id='
                            + $index
                            + '] .swatch-attribute-options [data-option-id='
                            + $vl
                            + ']').trigger('click');
                    } else {
                        $('.swatch-attribute[data-attribute-id='
                            + $index
                            + '] .swatch-attribute-options select').val($vl).trigger('change');
                    }
                } catch (e) {
                    console.log($.mage.__('Error when applied preselect product'));
                }
            });
            return true;
        },
        _ValidateQty: function ($widget) {
            var keymap, index,
                data = $widget.options.jsonChildProduct,
                config = $widget.options.jsonModuleConfig,
                state;
            $('input.input-text.qty').change(function () {
                index = $widget.findSwatchIndex($widget);
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
            if ($configImage === IMAGE_CONFIG_DISABLED) {
                return;
            }
            var images = this.options.jsonChildProduct['image'],
                gallery = $(this.options.mediaGallerySelector).data('gallery');
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
                index = $widget.findSwatchIndex($widget),
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

    return $.bss.Sdcp;
});
