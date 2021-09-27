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
define([
    'jquery',
    'uiComponent',
    'mage/validation',
    'mage/template',
    'ko',
    'underscore',
    'Magento_Customer/js/customer-data',
    'Webkul_Rmasystem/js/action/order-details',
    'Webkul_Rmasystem/js/action/filter-order',
    'Magento_Ui/js/model/messageList',
    'Magento_Ui/js/modal/alert',
    'mage/translate'
], function (
    $,
    Component,
    validation,
    mageTemplate,
    ko,
    _,
    customerData,
    LoadOrderDetails,
    FilterGridAction,
    globalMessageList,
    alert
) {
        'use strict';

        return Component.extend({
            items: ko.observableArray([]),
            itemDetails: ko.observableArray([]),
            checkAll: ko.observable(false),
            files: ko.observableArray([]),
            totalImages: ko.observable(0),
            resolutionType: ko.observableArray([]),
            resolutionValue: ko.observable(),
            deliveryStatus: ko.observableArray([]),
            deliveryValue: ko.observable(),
            rmaType: ko.observable(1),
            orderStatus: ko.observable(),
            selectedDelivery: ko.observable(),
            empty: ko.observable(0),
            initialize: function () {
                this._super();
                var self = this;
                var orderDetails = window.rmaDataConfig.orderDetails;
                var type = {};
                type['0'] = 'Refund';
                type['1'] = 'Exchange'

                $.each(orderDetails, function (index, value) {
                    self.items.push(value);
                });

                self.checkAll = ko.computed({
                    read: function () {
                        var items = self.itemDetails();
                        for (var i = 0, l = items.length; i < l; i++) {
                            if (!items[i].isSlected()) {
                                return false;
                            }
                        }
                        return true;
                    },
                    write: function (value) {
                        ko.utils.arrayForEach(self.itemDetails(), function (item) {
                            item.isSlected(value);
                        });
                    }
                });

                this.disableElement = ko.pureComputed(function () {
                    return self.orderStatus() == 'pending' ? "disable-element" : "";
                });

                // Private function
                function getColumnsForScaffolding(data)
                {
                    if ((typeof data.length !== 'number') || data.length === 0) {
                        return [];
                    }
                    var columns = [];
                    for (var propertyName in data[0]) {
                        columns.push({ headerText: propertyName, rowText: propertyName });
                    }
                    return columns;
                }


                ko.simpleGrid = {
                    // Defines a view model class you can use to populate a grid
                    viewModel: function (configuration) {
                        var thisSelf = this;
                        this.data = configuration.data;
                        this.currentPageIndex = ko.observable(0);
                        this.pageSize = 5;
                        this.orderFilter = ko.observable();
                        this.priceFilter = ko.observable();
                        this.dateFilter = ko.observable();
                        this.dsplayOrderError = ko.observable(false);;
                        this.filterResultError = ko.observable(false);
                        this.totalRecords = ko.observable(self.items().length);
                        if (self.items().length == 0) {
                            this.dsplayOrderError(true);
                        }

                        // If you don't specify columns configuration, we'll use scaffolding
                        this.columns = configuration.columns || getColumnsForScaffolding(ko.utils.unwrapObservable(this.data));

                        this.itemsOnCurrentPage = ko.computed(function () {
                            var startIndex = this.pageSize * this.currentPageIndex();
                            return ko.utils.unwrapObservable(this.data).slice(startIndex, startIndex + this.pageSize);
                        }, this);

                        this.maxPageIndex = ko.computed(function () {
                            return Math.ceil(ko.utils.unwrapObservable(this.data).length / this.pageSize) - 1;
                        }, this);

                        // sort order grid
                        this.sort = function (data, event) {
                            var element = event.currentTarget;
                            self._refreshSelectedHeader($(element).parents('thead')[0]);
                            $(element).addClass('wk_rma_selected');
                            self.sortItems(element.id);
                        };
                        this.filterGrid = function () {
                            var filterData = {};
                            filterData.orderFilter = thisSelf.orderFilter();
                            filterData.priceFilter = thisSelf.priceFilter();
                            filterData.dateFilter = thisSelf.dateFilter();
                            FilterGridAction(filterData).done(function (response) {
                                var data = $.parseJSON(response);
                                self.items(data.orderDetails);
                                if (self.items().length == 0) {
                                    thisSelf.filterResultError(true);
                                } else {
                                    thisSelf.filterResultError(false);
                                }
                            });
                        }
                        // define initial sorting
                        this.intializeSorting = function () {
                            var sortingData = customerData.get("sorting-data")();
                            var column = sortingData.column;
                            if ($.isEmptyObject(sortingData)) {
                                $(".wk_rma_sorter th").eq(0).addClass("wk_rma_selected");
                                $(".wk_rma_sorter th").eq(0).find("span").eq(1).addClass("wk_rma_asc");
                                self.items(_.sortBy(self.items(), column));
                            } else {
                                $('#' + column).addClass('wk_rma_selected');
                                $('#' + column).eq(0).find("span").eq(1).addClass("wk_rma_asc");
                                if (sortingData.direction == 'wk_rma_asc') {
                                    self.items(_.sortBy(self.items(), column));
                                } else {
                                    self.items(_.sortBy(self.items(), column));
                                    self.items.reverse();
                                }
                            }
                        };

                        this.loadOrderDetail = function (data, event) {
                            LoadOrderDetails(data).always(function (response) {
                                var data = $.parseJSON(response);
                                var messageContainer = globalMessageList;
                                if (response.status == 401) {
                                    window.location.replace(url.build('customer/account/login/'));
                                }
                                self.itemDetails([]);
                                self.resolutionType(data.resolutionsTypes);
                                self.deliveryStatus(data.deliverStatus);
                                self.rmaType(data.rmaType);
                                self.orderStatus(data.orderStatus);
                                if (!data.orderDetails.length) {
                                    self.empty(1);
                                } else {
                                    self.empty(0);
                                }
                                $.each(data.orderDetails, function (index, value) {
                                    value.isSlected = ko.observable(false);
                                    value.isSlected.subscribe(function (newvalue) {
                                        self.createCurrentRowRequired(newvalue, value);
                                    });
                                    self.itemDetails.push(value);
                                });
                                
                                if (!data.deliverable) {
                                    $('.wk-delivery').css('display', 'none');
                                } else {
                                    $('.wk-delivery').css('display', 'block');
                                }
                            });
                            var element = event.currentTarget;
                            $(element).find('input.wk_rma_order_selection').attr('checked', true);
                            return true;
                        };
                    }
                };

                var templateEngine = new ko.nativeTemplateEngine();

                templateEngine.addTemplate = function (templateName, templateMarkup) {
                    document.write(document.getElementById("ko_simpleGrid_grid"));
                    document.write(document.getElementById("ko_simpleGrid_pageLinks"));
                };
                // The "simpleGrid" binding
                ko.bindingHandlers.simpleGrid = {
                    init: function () {
                        return { 'controlsDescendantBindings': true };
                    },
                    // This method is called to initialize the node, and will also be called again if you change what the grid is bound to
                    update: function (element, viewModelAccessor, allBindingsAccessor) {
                        var viewModel = viewModelAccessor(), allBindings = allBindingsAccessor();

                        // Empty the element
                        while (element.firstChild) {
                            ko.removeNode(element.firstChild);
                        }

                        // Allow the default templates to be overridden
                        var gridTemplateName = allBindings.simpleGridTemplate || "ko_simpleGrid_grid",
                            pageLinksTemplateName = allBindings.simpleGridPagerTemplate || "ko_simpleGrid_pageLinks";

                        // Render the main grid
                        var gridContainer = element.appendChild(document.createElement("DIV"));
                        ko.renderTemplate(gridTemplateName, viewModel, { templateEngine: templateEngine }, gridContainer, "replaceNode");

                        // Render the page links
                        var pageLinksContainer = element.appendChild(document.createElement("DIV"));
                        ko.renderTemplate(pageLinksTemplateName, viewModel, { templateEngine: templateEngine }, pageLinksContainer, "replaceNode");
                    }
                };
                this.gridViewModel = new ko.simpleGrid.viewModel({
                    data: self.items,
                    columns: [
                        { headerText: "", rowText: "radio" },
                        { headerText: $.mage.__("Order Id"), rowText: "increment_id" },
                        { headerText: $.mage.__("Price"), rowText: "grand_total_format" },
                        { headerText: $.mage.__("Order Date"), rowText: "date" },
                    ],
                    pageSize: 5
                });
                $(document).on('keypress','.filter_input', function (e) {
                    if (e.which == 13) {
                        e.preventDefault();
                        $('.filter_input').blur(); 
                        $('.action-secondary.filter-order').trigger('click');
                    }
                });
            },
            disableAllElement: function () {
                $('#wk_rma_order_details').find('input, textarea, button, select').attr('disabled', 'disabled');
            },
            createCurrentRowRequired: function (newvalue, value) {
                if (newvalue === true) {
                    $('#qty_' + value.itemid).addClass('required');
                    $('#reason_' + value.itemid).addClass('required');
                } else {
                    $('#qty_' + value.itemid).removeClass('required');
                    $('#reason_' + value.itemid).removeClass('mage-error');
                    $('#reason_' + value.itemid).removeClass('required');
                }
            },
            addImageBlock: function (event) {
                var templateEngine = new ko.nativeTemplateEngine();

                templateEngine.addTemplate = function (templateName, templateMarkup) {
                    document.write(document.getElementById("rma-image-template"));
                };
                var parentDiv = $('#image_block').clone();
                var imageContainer = $('body').find('#image_block').append(document.createElement("DIV"));
                ko.renderTemplate('rmaImageTemplate', this, { templateEngine: templateEngine }, imageContainer, "replaceNode");
                $(parentDiv).insertAfter('.image-field button');

            },
            sortItems: function (elementId) {
                var sortingData = {};
                var sortingElement = $('#' + elementId).find('span').eq(1);
                sortingData.column = elementId;

                if (sortingElement.hasClass('wk_rma_desc') === true) {
                    sortingElement.addClass('wk_rma_asc');
                    sortingElement.removeClass('wk_rma_desc');
                    sortingData.direction = 'wk_rma_asc';
                    if (elementId == 'grand_total_format') {
                        elementId = 'grand_total';
                    }
                    this.items(_.sortBy(this.items(), elementId));
                    this.items.reverse();
                } else if (sortingElement.hasClass('wk_rma_asc') === true) {
                    sortingElement.addClass('wk_rma_desc');
                    sortingElement.removeClass('wk_rma_asc');
                    sortingData.direction = 'wk_rma_desc';
                    if (elementId == 'grand_total_format') {
                        elementId = 'grand_total';
                    }
                    this.items(_.sortBy(this.items(), elementId));
                } else {
                    $("#" + elementId).parents('thead').find('th').each(function () {
                        $(this).find("span").eq(1).attr("class", "");
                    });
                    sortingElement.addClass('wk_rma_asc');
                    sortingData.direction = 'wk_rma_asc';
                    if (elementId == 'grand_total_format') {
                        elementId = 'grand_total';
                    }
                    this.items(_.sortBy(this.items(), elementId));
                    this.items.reverse();
                }
                customerData.set("sorting-data", sortingData);
            },
            _refreshSelectedHeader: function (element) {
                $(element).find('th').removeClass('wk_rma_selected');
            },
            saveRma: function (event) {
                var isSlectedAnyItem = false;
                ko.utils.arrayForEach(this.itemDetails(), function (items) {
                    if (items.isSlected() === true) {
                        isSlectedAnyItem = true;
                    }
                });

                if (isSlectedAnyItem === false && this.rmaType() !== 0) {
                    $('<div />').html($.mage.__('No item(s) selected for return.'))
                        .modal({
                            title: $.mage.__('Attention'),
                            autoOpen: true,
                            buttons: [{
                                text: 'Cancel',
                                attr: {
                                    'data-action': 'cancel'
                                },
                                'class': 'action',
                                click: function () {
                                    this.closeModal();
                                }
                            }]
                        });
                } else {
                    $('#save_rma_form').submit();
                }
            },
            deleteImage: function (data, event) {
                $(event.currentTarget).parent('div').remove();
                if ($(event.currentTarget).siblings('img').length > 0) {
                    this.totalImages(this.totalImages() - 1);
                }
            },
            fileSelect: function (elemet, event) {
                this.totalImages(this.totalImages() + 1);
                var self = this;
                var files = event.target.files;// FileList object
                // Loop through the FileList and render image files as thumbnails.
                for (var i = 0, f; f = files[i]; i++) {
                    // Only process image files.
                    if (!f.type.match('image.*')) {
                        alert({
                            content: $.mage.__('This file type is not supported, allowed extensions are: png, jpg, jpeg.'),

                        });
                        continue;
                    }
                    var url = window.URL || window.webkitURL;
                    var image = new Image();
                    image.onload = function () {
                        $(event.currentTarget).parent('div').find('.wk-default-block').remove();
                        $(event.currentTarget).parent('div').append(image);
                    }
                    image.onerror = function () {
                        self.totalImages(self.totalImages() - 1);
                        $(event.currentTarget).parent('div').remove();
                        $('#wk_rma_label_image').trigger('click');
                        alert({
                            content: $.mage.__('This file type is not supported, allowed extensions are: png, jpg, jpeg.'),

                        });
                    };
                    image.src = url.createObjectURL(f);
                    // Closure to capture the file information.
                }
            },
        });
    });
