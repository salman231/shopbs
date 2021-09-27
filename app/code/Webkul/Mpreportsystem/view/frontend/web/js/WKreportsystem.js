/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Mpreportsystem
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
/*jshint jquery:true*/
define([
    "jquery",
    'mage/translate',
    'Magento_Ui/js/modal/alert',
    'handlebars',
    "jquery/ui",
    "mage/calendar",
], function ($, $t, alert, $h) {
    'use strict';
    $.widget('mage.WKreportsystem', {
        options: {
            backUrl: ''
        },
        _create: function () {
            var self = this;
            self.addCalender();
            self.addCategoryAndStatusBlock();
            var categoryWidth = $('.wk-select-box').outerWidth();
            if (!window.matchMedia('(max-width: 687px)').matches) {
                $('.wk_orders-options').css('left', (categoryWidth + 30)+'px');
            }
            $(self.options.wkSelectBox).on('click', function (event) {
                event.stopPropagation();
                self.updatecssOfSelectBox($(this));
            });
            $(self.options.categorymultiselect).on('change', function () {
                self.changeCategoryCount($(this));
            });
            $(self.options.ordermultiselect).on('change', function () {
                self.changeOrderstatusCount($(this));
            });
            $('body').on('click', function (event) {
                var display = $(this).find(".wk_orders-options .wk-orderstatus-dropdown");
                if (display.css("display") == 'block') {
                    $(display.parents('.wk-order-status').find('.wk-select-box')).trigger('click');
                }
                var display = $(this).find(".wk_categories-options .wk-category-dropdown");
                if (display.css("display") == 'block') {
                    $(display.parents('.wk-categories').find('.wk-select-box')).trigger('click');
                }
            });
            $(self.options.topsellingproductajax).on('change', function () {
                $(self.options.productpiChart).attr('src',self.options.loaderimage);
                var data = self.getsalesFilterData();
                self.topselleingProductAjaxRequest($(this), data);
            });
            $(self.options.geolocationfilterajax).on('change', function () {
                $(self.options.geolocationChart).attr('src',self.options.loaderimage);
                var data = self.getsalesFilterData();
                self.geolocationAjaxRequest($(this), data);
            });
            $(self.options.salesfilterajax).on('click', function () {
                $(self.options.saleschart).attr('src',self.options.loaderimage);
                $(this).parents('.wk-filter-buttons').find(
                    '.wk-selected-filter-button'
                ).removeClass(
                    'wk-selected-filter-button'
                ).addClass(
                    'wk-filter-button'
                );
                $(this).addClass('wk-selected-filter-button').removeClass('.wk-filter-button');
                var data = self.getsalesFilterData();
                data['filter'] = $(this).attr('value');
                self.salesChartAjaxRequest(data);
            });
            $(self.options.crossfilterajax).on('click', function () {
                self.updateFilterDataOnCross($(this));
            });
            $(self.options.wk_filter_btn).on('click', function (e) {
                e.preventDefault();
                var data = self.getsalesFilterData();
                var startfrom = $('#wk_report_date_start').val();
                var endto = $('#wk_report_date_end').val();
                if (startfrom != '' && endto != '' && new Date(startfrom) <= new Date(endto)) {
                    data['start_from'] = startfrom;
                    data['end_to'] = endto;
                    data['grid'] = 1;
                    self.salesCollectionAjaxRequest(data);
                } else {
                    if (startfrom != '' && endto != '') {
                        alert({
                            content: self.options.dateErrorMessage
                        });
                    } else {
                        data['grid'] = 1;
                        self.salesCollectionAjaxRequest(data);
                    }
                }
            });
        },
        addCalender:function () {
            var self = this;
            $("#wk_report_date_start").calendar({'dateFormat':'m/d/y'});
            $("#wk_report_date_end").calendar({'dateFormat':'m/d/y'});
        },
        addCategoryAndStatusBlock:function () {
            var categoryhtml = $('.wk-category-dropdown');
            $('.wk_categories-options').append(categoryhtml);
            var orderhtml = $('.wk-orderstatus-dropdown');
            $('.wk_orders-options').append(orderhtml);
        },
        changeCategoryCount:function (selectbox) {
            var categoryValues = selectbox.val();
            var count = 0;
            if (categoryValues) {
                count = categoryValues.length;
            }
            var text = $t("Choose categories ( ")+count+' )';
            $('.wk-categories-label').text(text);
        },
        changeOrderstatusCount:function (selectbox) {
            var ordreValues = selectbox.val();
            var count = 0;
            if (ordreValues) {
                count = ordreValues.length;
            }
            var text = $t("Choose Order Status ( ")+count+' )';
            $('.wk-order-label').text(text);
        },
        addCategoryBlock: function (thisthis) {
            var self = this;
            $('.wk_categories-options .wk-category-dropdown').show();
        },
        removeCategoryBlock: function (thisthis) {
            var self = this;
            $('.wk_categories-options .wk-category-dropdown').hide();
        },
        addOrderStatusBlock: function (thisthis) {
            var self = this;
            $('.wk_orders-options .wk-orderstatus-dropdown').show();
        },
        removeOrderStatusBlock: function (thisthis) {
            var self = this;
            $('.wk_orders-options .wk-orderstatus-dropdown').hide();
        },
        geolocationAjaxRequest:function (element, data1) {
            var self = this;
            var filter = element;
            var categories = data1['categories'];
            var orderstatus = data1['orderstatus'];
            $.ajax({
                url         :   self.options.geolocationfilterurl,
                data        :   {filter:filter.attr('value'),categories:categories,orderstatus:orderstatus},
                type        :   "post",
                dataType    :   "json",
                success     :   function (data) {
                    $(self.options.geolocationChart).attr('src', data);
                },
                error: function (data) {
                    alert({
                        content: self.options.ajaxErrorMessage
                    });
                    window.location.href = self.options.indexurl;
                }
            });
        },
        topselleingProductAjaxRequest:function (element, data1) {
            var self = this;
            var filter = element;
            var categories = data1['categories'];
            var orderstatus = data1['orderstatus'];
            $.ajax({
                url         :   self.options.topsellingfilterurl,
                data        :   {filter:filter.attr('value'),categories:categories,orderstatus:orderstatus},
                type        :   "post",
                dataType    :   "json",
                success     :   function (data) {
                    $(self.options.productpiChart).attr('src', data);
                },
                error: function (data) {
                    alert({
                        content: self.options.ajaxErrorMessage
                    });
                    window.location.href = self.options.indexurl;
                }
            });
        },
        salesChartAjaxRequest: function (data1) {
            var self = this;
            $.ajax({
                url         :   self.options.salesfilterurl,
                data        :   {data:data1},
                type        :   "post",
                dataType    :   "json",
                success     :   function (data) {
                    $(self.options.saleschart).attr('src',data);
                },
                error: function (response) {
                    alert({
                        content: self.options.ajaxErrorMessage
                    });
                    window.location.href = self.options.indexurl;
                }
            });
            
        },
        salesCollectionAjaxRequest: function (data1, url) {
            $('#generate-report').submit();
        },
        getsalesFilterData: function () {
            var self = this;
            var categories = {};
            var orderstatus = {};
            var i = 0;
            $('.wk-filter-data-div.wk-border-blue').each(function () {
                categories[i] = $(this).find('.wk-filter-cross').attr('value');
                i++;
            });
            i = 0;
            $('.wk-filter-data-div.wk-border-green').each(function () {
                orderstatus[i] = $(this).find('.wk-filter-cross').attr('value');
                i++;
            });
            var data = {};
            data['categories'] = categories;
            data['orderstatus'] = orderstatus;
            return data;
        },
        updateFilterDataOnCross:function (element) {
            if (element.hasClass('wk-bg-blue-color')) {
                var value = element.attr('value');
                $('#wk-category-multi-select option[value='+value+']').prop('selected', false);
                $('#wk-category-multi-select').trigger('change');
                element.parents('.wk-filter-data-div').remove();
            } else {
                var value = element.attr('value');
                $('#wk-order-multi-select option[value='+value+']').prop('selected', false);
                $('#wk-order-multi-select').trigger('change');
                element.parents('.wk-filter-data-div').remove();
            }
        },
        updatecssOfSelectBox:function (element) {
            var self = this;

            if (element.find('.wk-select-arrow-up').length > 0) {
                var arrowLabel = element.find('.wk-select-arrow-up');
                arrowLabel.addClass('wk-select-arrow-down');
                arrowLabel.removeClass('wk-select-arrow-up');
                if (element.parents('.wk-categories').length > 0) {
                    self.addCategoryBlock(element);
                }
                if (element.parents('.wk-order-status').length > 0) {
                    self.addOrderStatusBlock(element);
                }
            } else if (element.find('.wk-select-arrow-down').length > 0) {
                var arrowLabel = element.find('.wk-select-arrow-down');
                arrowLabel.addClass('wk-select-arrow-up');
                arrowLabel.removeClass('wk-select-arrow-down');
                if (element.parents('.wk-categories').length > 0) {
                    self.removeCategoryBlock(element);
                }
                if (element.parents('.wk-order-status').length > 0) {
                    self.removeOrderStatusBlock(element);
                }
            }
        }
    });
    return $.mage.WKreportsystem;
});