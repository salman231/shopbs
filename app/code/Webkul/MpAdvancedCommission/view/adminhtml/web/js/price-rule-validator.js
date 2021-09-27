/**
 * @category   Webkul
 * @package    Webkul_MpAdvancedCommission
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
 /*jshint jquery:true*/
 define([
    'jquery',
    'mage/translate',
    'jquery/validate'
], function ($) {
    // validation for price range
    $.validator.addMethod(
        'validate-max-min-check',
        function (value) {
           var priceFrom  = document.getElementById('commissionrules_price_from').value;
           if (parseInt(priceFrom) < parseInt(value)) {
               return true;
           }
        },
        $.mage.__('Product Price To must be greater than Product Price From.')
    );

    // validation for percent commission price
    $.validator.addMethod(
        'percent-amount-check',
        function (value) {
            var commissionType  = document.getElementById('commissionrules_commission_type').value;
            var priceFrom = document.getElementById('commissionrules_price_from').value;
            if (commissionType === 'fixed' && parseInt(priceFrom) >= parseInt(value)) {
                return true;
            } else if (commissionType === 'percent' && parseInt(value) <= 100) {
                return true;
            }
        },
        $.mage.__('In case of percent commission, commission value must be less than or equal to 100 and value must be less than or equal to product price from in case fixed commission.')
    );
});
