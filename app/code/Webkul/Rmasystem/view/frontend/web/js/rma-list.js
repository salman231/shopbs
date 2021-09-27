/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Rmasystem
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
/*jshint jquery:true*/
define([
        "jquery",
        'mage/translate',
        "mage/template",
        "mage/mage",
        "mage/calendar",
    ], function ($, $t,mageTemplate, alert) {
        'use strict';
        $.widget('mage.rmaList', {
            _create: function () {
                var self = this;
                $("#wk_rma_filter_date").calendar({
                    dateFormat:'Y-mm-dd',
                });
                if (this.options.sortingColumn == null || this.options.sortingDirection == null) {
                    $(this.options.orderTableSort+" th").eq(0).addClass("wk_rma_selected");
                    $(this.options.orderTableSort+" th").eq(0).find("span").eq(1).addClass("wk_rma_asc");
                } else {
                    $(this.options.orderTableSort+" th").each(function () {
                        var this_span = $(this).find("span").eq(1);
                        if (this_span.attr("data-attr") == self.options.sortingColumn) {
                            if (self.options.sortingDirection == "ASC") {
                                this_span.addClass("wk_rma_asc");
                            } else {
                                this_span.addClass("wk_rma_desc");
                            }
                            $(this).addClass("wk_rma_selected");
                        }
                    });
                }

                $(this.options.orderColumnSort).on("click",function () {
                    var this_th = $(this);
                    self._sortOrderTable(this_th);
                });

                $(this.options.filterColumn).on("click",function () {
                    self._filterTable();
                });
                $(this.options.cancelButton).click(function (event) {
                    event.preventDefault();
                    self._cancleRma($(this));
                });
                $(this.options.changeDeliveryStatus).on("change",function () {
                    if ($(this).val() == 1) {
                        $("#wk_rma_consignment_no").removeAttr("disabled").attr("class","required-entry");
                        $("#wk_rma_depends_del_status").show();
                    } else {
                        $("#wk_rma_consignment_no").attr("disabled","disabled").attr("class","");
                        $("#wk_rma_depends_del_status").hide();
                    }
                });
            },
            _cancleRma: function (currentRma) {
                var self = this;
                var href = $(currentRma).attr('href');
                $('<div />').html('Are you sure want to cancel it?')
                .modal({
                    title: 'Cancel RMA',
                    autoOpen: true,
                    buttons: [{
                        text: 'Confirm',
                        attr: {
                            'data-action': 'confirm'
                        },
                        'class': 'action subscribe primary',
                        click: function () {
                                this.closeModal();
                                window.location.href = href;
                            }
                    },{
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
            },
            _filterTable: function () {
                var self = this;
                $.ajax({
                    url: self.options.filterUrl,
                    type: "POST",
                    showLoader: true,
                    data: {
                        rma_id    :   $("#wk_rma_filter_rma_id").val(),
                        order_id  :   $("#wk_rma_filter_order_id").val(),
                        status    :   $("#wk_rma_filter_status").val(),
                        date      :   $("#wk_rma_filter_date").val()
                    },
                    success: function () {
window.location.href = "";}
                });
            },
            _sortOrderTable: function (thisColumn) {
                var self = this;
                $(self.options.orderTableSort+" th").removeClass("wk_rma_selected");
                thisColumn.addClass("wk_rma_selected");
                var sortSpan = thisColumn.find("span").eq(1);
                var sortClass = sortSpan.attr("class");
                if (sortClass == "wk_rma_asc") {
                    sortSpan.attr("class","wk_rma_desc");
                } else if (sortClass == "wk_rma_desc") {
                    sortSpan.attr("class","wk_rma_asc");
                } else if (sortClass == "") {
                    $(self.options.orderTableSort+" th").each(function () {
                        $(this).find("span").eq(1).attr("class","");
                    });
                    sortSpan.attr("class","wk_rma_asc");
                }
                var direction = "DESC";
                var attr = sortSpan.attr("data-attr");
                if (sortSpan.attr("class") == "wk_rma_asc") {
                    direction = "ASC";
                }
                $.ajax({
                    url: self.options.sortUrl,
                    type: "POST",
                    showLoader: true,
                    data: {
                        attr        :   attr,
                        direction   :   direction
                    },
                    success: function () {
window.location.href = "";}
                });

            },
        });
    return $.mage.rmaList;
    });
