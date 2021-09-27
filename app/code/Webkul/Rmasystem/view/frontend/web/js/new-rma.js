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
        $.widget('mage.newRma', {
            _create: function () {
                var self = this;
                $("#wk_rma_filter_date").calendar({
                    dateFormat:'Y-m-dd',
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

                $(this.options.selectOrder).on('click',function () {
                    var radio_check=$(this).children('td').last().children().prop('checked', true);
                    self._checkOrder(radio_check);
                });

                $(this.options.selectItem).on('click',function () {
                    var radio_check=$(this).children('td').last().children().prop('checked', true);
                    self._checkItem(radio_check);
                });
                $(this.options.selectAll).on('change',function () {
                    self._selectAll($(this));
                });

                $('body').on('change', this.options.childCheckBox ,function () {
                    console.log();
                    if ($(this).is(":checked")) {
                        $(this).parent('td').siblings('td:nth-last-child(2)').find('.item_reason').attr('data-validate','{required:true}');
                        $(this).parent('td').siblings('td:nth-last-child(3)').find('.return_item').attr('data-validate','{required:true}');
                    } else {
                        $(this).parent('td').siblings('td:nth-last-child(2)').find('.item_reason').removeAttr('data-validate');
                        $(this).parent('td').siblings('td:nth-last-child(3)').find('.return_item').removeAttr('data-validate');
                    }

                });


                $(this.options.orderColumnSort).on("click",function () {
                    var this_th = $(this);
                    self._sortOrderTable(this_th);
                });

                $(this.options.filterColumn).on("click",function () {
                    self._filterTable();
                });
                $(this.options.submitButton).click(function (event) {
                    event.preventDefault();
                    self._submitForm();
                });
                $('.wk_rma_image_container').on('click', '.wk-logoimagedelete', function () {
                    $(this).parentsUntil('.wk_rma_image_cover').remove();
                });
                $('#related_images').on('change',function () {
                    self._halfUpload($(this));
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
            _submitForm: function () {
                var self = this;
                var itemChecked = false;
                console.log($(self.options.orderDetailTable).find(self.options.childCheckBox));
                $(self.options.orderDetailTable).find(self.options.childCheckBox).each(function () {
                    if ($(this).is(":checked")) {
                        itemChecked = true;
                    }
                });

                if (itemChecked == false) {
                    $('<div />').html('No item(s) selected for return.')
                    .modal({
                        title: $t('Attention'),
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
            _halfUpload: function (currentImage) {
                var self = this;
                $(".wk_rma_image_container").html("");
                for (var i=0; i<currentImage[0].files.length; i++) {
                    if (currentImage[0].files && currentImage[0].files[i]) {
                        var reader = new FileReader();
                        reader.onload = function (e) {
                            $(".wk_rma_image_container").append("<span class='wk_rma_image_cover'><div class='img-actions'><span class='wk-logoimagedelete'><img src = '"+self.options.deleteIconUrl+"' style='width:15px' /></span><img class='wk_rma_image' src='"+e.target.result+"'/></span>");
                        }
                        reader.readAsDataURL(currentImage[0].files[i]);
                    }
                }
            },
            _filterTable: function () {
                var self = this;
                $.ajax({
                    url: self.options.filterUrl,
                    type: "POST",
                    showLoader: true,
                    data: {
                        order_id    :   $("#wk_rma_filter_order_id").val(),
                        price       :   $("#wk_rma_filter_price").val(),
                        date        :   $("#wk_rma_filter_date").val()
                    },
                    success: function () {
                        window.location.href = "";
                    }
                });
            },
            _sortOrderTable: function (thisColumn) {
                var self = this;
                $(self.options.orderTableSort+" th").removeClass("wk_rma_selected");
                console.log(thisColumn);
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
            _selectAll: function (thisSelect) {
                var self = this;
                 if ($(thisSelect).is(":checked") == true) {
                    $(self.options.orderDetailTable).find(self.options.childCheckBox).prop("checked",true);
                } else {
$(self.options.orderDetailTable).find(self.options.childCheckBox).prop("checked",false);
                }
            },
            _checkItem: function (radioCheck) {
                var self = this;
                var incrementId = $(radioCheck).attr("data-inc_id");
                var orderid = $(radioCheck).attr("data-orderid");

                $.ajax({
                    url     : self.options.itemDetailsUrl,
                    type    : "POST",
                    showLoader: true,
                    dataType: "json",
                    data    : { order_id:orderid, },
                    success:function (content) {
                        if (content.length == 0) {
                            $(self.options.orderDetailTable).find("tbody").html("<tr><td colspan='7'>"+$t('No order selected')+"</td></tr>");
                        } else {
                            $(self.options.orderDetailTable).find("tbody").html('');

                            var progressTmpl = mageTemplate(self.options.detailTemplate),
                                      tmpl;

                            var row = "<input type='hidden' name='order_id' value='"+orderid+"'/><input type='hidden' name='increment_id' value='"+incrementId+"'/>";
                            $(self.options.orderDetailTable).find("tbody").html(row);
                            for (var i=0; i<content.length; i++) {
                                tmpl = progressTmpl({
                                    data: {
                                        url: content[i].url,
                                        name: content[i].name,
                                        sku: content[i].sku,
                                        itemid: content[i].itemid,
                                        productid: content[i].product_id,
                                        qty: content[i].qty,
                                        price: content[i].price,
                                        returnedQty: content[i].returnedQty,
                                        disabled: content[i].disabled
                                    }
                                });
                                $(self.options.orderDetailTable).find("tbody").append(tmpl);
                            }
                        }
                    }
                });
            },
            _checkOrder: function (radioCheck) {
                var self = this;
                var incrementId = $(radioCheck).attr("data-inc_id");
                var orderid = $(radioCheck).attr("data-orderid");

                $.ajax({
                    url     : self.options.orderDetailsUrl,
                    type    : "POST",
                    showLoader: true,
                    dataType: "json",
                    data    : { order_id:orderid, },
                    success:function (content) {
                        if (content.length == 0) {
                            $(self.options.orderDetailTable).find("tbody").html("<tr><td colspan='7'>"+$t('No order selected')+"</td></tr>");
                        } else {
                            $(self.options.orderDetailTable).find("tbody").html('');

                            var progressTmpl = mageTemplate(self.options.detailTemplate),
                                      tmpl;

                            var row = "<input type='hidden' name='order_id' value='"+orderid+"'/><input type='hidden' name='increment_id' value='"+incrementId+"'/>";
                            $(self.options.orderDetailTable).find("tbody").html(row);
                            for (var i=0; i<content.length; i++) {
                                tmpl = progressTmpl({
                                    data: {
                                        url: content[i].url,
                                        name: content[i].name,
                                        sku: content[i].sku,
                                        itemid: content[i].itemid,
                                        productid: content[i].product_id,
                                        qty: content[i].qty,
                                        price: content[i].price,
                                    }
                                });
                                $(self.options.orderDetailTable).find("tbody").append(tmpl);
                            }
                        }
                    }
                });
            },
        });
    return $.mage.newRma;
    });
