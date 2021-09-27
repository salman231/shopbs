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
    'ko'
], function (
    $,
    Component,
    validation,
    mageTemplate,
    ko
) {
        'use strict';
        return Component.extend({
            paymentType: ko.observableArray([]),
            showPayment: ko.observable(0),
            showPartialField: ko.observable(false),
            paymentTypeChange: ko.observable(),
            initialize: function () {
                this._super();
                var self = this;
                $('body').on('#save', 'click', function () {
                    $('#edit_form').submit();
                });
                this.paymentType.push({ 'label': 'Full Payment', 'value': '1' });
                this.paymentType.push({ 'label': 'Partial Payment', 'value': '2' });
                this.paymentTypeChange.subscribe(function (newValue) {
                    self.showPartialField(false);
                    if (newValue == 2) {
                        self.showPartialField(true);
                    }
                });
            },
            deliverStatusChange: function (data, event) {
                console.log($(event.currentTarget));
                if ($(event.currentTarget).val() == 1) {
                    $('#wk_rma_depends_del_status').show();
                } else {
                    $('#wk_rma_depends_del_status').hide();
                }
            },
            onLoadDeliverStatusChange: function (data) {
                if ($('#wk_rma_delivery_status').val() == 1) {
                    $('#wk_rma_depends_del_status').show();
                } else {
                    $('#wk_rma_depends_del_status').hide();
                }
            },
            statusChange: function (data, event) {
                if ($(event.currentTarget).val() == 5 || $(event.currentTarget).val() == 0) {
                    $('.ship_label').attr('disabled', 'disabled');
                    $('.ship_label').hide();
                } else {
                    $('.ship_label').show();
                    $('.ship_label').removeAttr('disabled');
                }
            },
            onLoadStatusChange: function (data) {
                if ($('.select_status').val() == 5 || $('.select_status').val() == 0) {
                    $('.ship_label').hide();
                } else {
                    $('.ship_label').show();
                }
            },
            booleanValue: function (data, event) {
                if ($(event.currentTarget).is(":checked")) {
                    this.showPayment(this.showPayment() + 1);
                    $(event.currentTarget).val(1);
                    return true;
                } else {
                    $(event.currentTarget).val(0);
                    this.showPayment(this.showPayment() - 1);
                    return false;
                }

            }
        });
    });
