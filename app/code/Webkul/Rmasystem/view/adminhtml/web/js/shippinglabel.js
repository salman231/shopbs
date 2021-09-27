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
require([
    'jquery',
], function (
    $
) {
    $(document).ready(function () {
        if (jQuery(".delete-image").length) {
            jQuery(".delete-image").remove();
        } else {
            jQuery("#shippinglabel_filename").addClass("required-entry");
        }

    });
});