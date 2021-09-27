/**
 * @category   Webkul
 * @package    Webkul_MpAdvancedCommission
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
 /*jshint jquery:true*/
define([
    "jquery",
    'mage/translate',
    'Magento_Ui/js/modal/alert',
    "jquery/ui"
], function ($, $t, alert) {
    'use strict';
    $.widget('mage.categoryCommission', {
        options: {
            ajaxErrorMessage: $t('There was error during fetching results.')
        },
        _create: function () {
            var self = this;
            
            $("#mpadvancedcommission_options_category_commission").on("click",function () {
                new Ajax.Request(self.options.checkAjaxUrl, {
                    method:     'POST',
                    onSuccess: function (transport) {
                        $('#light').show();
                        $('#fade').show();
                    },
                    onFailure: function () {
                        alert({
                            content: self.options.ajaxErrorMessage
                        });
                    }
                });
            });

            $("#fade").on("click",function () {
                $('#light').hide();
                $('#fade').hide();
            });

            $("body").on('click','#mpadvancedcommission-options-category-commission-save-button',function () {
                var categoryCommissionArr = {};
                var i=0;
                var commissionType = $('#mpadvancedcommission_options_commission_type').val();
                var numErrorMsg = $('#wk-admin-num-validation').val();
                var commissionTypeErrorMsg = $('#wk-admin-commission-type-validation').val();
                var flag = 1;
                $('.wk-category-commission').each(function () {
                    if ($(this).val()=='' || ($.isNumeric($(this).val()) && $(this).val()>=0) ) {
                        $(this).css('border-color','white');
                    } else {
                        $(this).css('border-color','red');
                        $(this).val('');
                        i++;
                    }
                    if (commissionType === 'percent') {
                        if (parseInt($(this).val()) > 100) {
                            flag = 0;
                            alert({
                                content: commissionTypeErrorMsg
                            });
                            return false;
                        }
                    }
                    categoryCommissionArr[$(this).attr('id')] = $(this).val();
                });
               
                if (i>0) {
                    alert({
                        content: numErrorMsg
                    });
                    return false;
                }

                if (flag) {
                    new Ajax.Request(self.options.categoryCommissionSaveUrl, {
                        method:     'POST',
                        parameters:{
                            object: JSON.stringify(categoryCommissionArr)
                        },
                        onSuccess: function (transport) {
                            $('#light').hide();
                            $('#fade').hide();
                        },
                        onFailure: function () {
                            alert({
                                content: self.options.ajaxErrorMessage
                            });
                        }
                    });
                }
            });

            $("body").delegate('.wk-plus ,.wk-plusend,.wk-minus, .wk-minusend ',"click",function () {
                var thisthis=$(this);
                if (thisthis.hasClass("wk-plus") || thisthis.hasClass("wk-plusend")) {
                    if (thisthis.hasClass("wk-plus")) {
                        thisthis.removeClass('wk-plus').addClass('wk-plus_click');
                    }
                    if (thisthis.hasClass("wk-plusend")) {
                        thisthis.removeClass('wk-plusend').addClass('wk-plusend_click');
                    }
                    thisthis.prepend("<span class='wk-node-loader'></span>");
                    self.callCategoryTreeAjaxFunction(thisthis);
                }
                if (thisthis.hasClass("wk-minus") || thisthis.hasClass("wk-minusend")) {
                    self.callRemoveCategoryNodeFunction(thisthis);
                }
            });
        },
        callCategoryTreeAjaxFunction: function (thisthis) {
            var self = this;
            var i, len, name, id, commission;
            new Ajax.Request(self.options.categoryTreeAjaxUrl, {
                method:     'POST',
                parameters    :   {
                    parentCategoryId:thisthis.siblings("input").attr('id')
                },
                onSuccess: function (transport) {
                    var newdata=  $.parseJSON(transport.responseText);
                    len = newdata.length;
                    var pxl= parseInt(thisthis.parent(".wk-cat-container").css("margin-left").replace("px",""))+20;
                    thisthis.find(".wk-node-loader").remove();
                    if (thisthis.attr("class") == "wk-plus") {
                        thisthis.attr("class","wk-minus");
                    }
                    if (thisthis.attr("class") == "wk-plusend") {
                        thisthis.attr("class","wk-minusend");
                    }
                    if (thisthis.attr("class") == "wk-plus_click") {
                        thisthis.attr("class","wk-minus");
                    }
                    if (thisthis.attr("class") == "wk-plusend_click") {
                        thisthis.attr("class","wk-minusend");
                    }
                    for (i=0; i<len; i++) {
                        id=newdata[i].id;
                        commission=newdata[i].commission;
                        name=newdata[i].name;
                        if (newdata[i].counting === 0) {
                            thisthis.parent(".wk-cat-container").after('<div class="wk-removable wk-cat-container" style="display:none;margin-left:'+pxl+'px;"><span  class="wk-no"></span><span class="wk-foldersign"></span><span class="wk-elements wk-cat-name">'+ name +'</span><input class="wk-elements wk-category-commission" type="text" name="category[]" id='+ id +' value='+ commission +'></div>');
                        } else {
                            thisthis.parent(".wk-cat-container").after('<div class="wk-removable wk-cat-container" style="display:none;margin-left:'+pxl+'px;"><span  class="wk-plusend"></span><span class="wk-foldersign"></span><span class="wk-elements wk-cat-name">'+ name +'</span><input class="wk-elements wk-category-commission" type="text" name="category[]" id='+ id +' value='+ commission +'></div>');
                        }
                    }
                    thisthis.parent(".wk-cat-container").nextAll().slideDown(300);
                },
                onFailure: function () {
                    alert({
                        content: self.options.ajaxErrorMessage
                    });
                }
            });
        },
        callRemoveCategoryNodeFunction: function (thisthis) {
            if (thisthis.attr("class") == "wk-minus") {
                thisthis.attr("class","wk-plus");
            }
            if (thisthis.attr("class") == "wk-minusend") {
                thisthis.attr("class","wk-plusend");
            }
            var thiscategory = thisthis.parent(".wk-cat-container");
            var marg= parseInt(thiscategory.css("margin-left").replace("px",""));
            while (thiscategory.next().hasClass("wk-removable")) {
                if (parseInt(thiscategory.next().css("margin-left").replace("px",""))>marg) {
                    thiscategory.next().slideUp("slow",function () {
                        $(this).remove();
                    });
                }
                thiscategory = thiscategory.next();
                if (typeof thiscategory.next().css("margin-left")!= "undefined") {
                    if (marg == thiscategory.next().css("margin-left").replace("px","")) {
                        break;
                    }
                }
            }
        }
    });
    return $.mage.categoryCommission;
});
