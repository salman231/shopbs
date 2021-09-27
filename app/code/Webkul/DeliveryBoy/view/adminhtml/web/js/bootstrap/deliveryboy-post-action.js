/**
 * Webkul Software.
 * 
 * PHP version 7.0+
 *
 * @category  Webkul
 * @package   Webkul_DeliveryBoy
 * @author    Webkul <support@webkul.com>
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html ASL Licence
 * @link      https://store.webkul.com/license.html
 */
require(
    [
        "jquery",
        "Magento_Ui/js/modal/confirm",
        "mage/translate"
    ],
    function ($, confirm) {
        "use strict";

        function getBannerForm(url) {
            return $("<form>", {
                    "action": url,
                    "method": "POST"
                })
                .append(
                    $(
                        "<input>", {
                            "name": "form_key",
                            "value": window.FORM_KEY,
                            "type": "hidden"
                        }
                    )
                );
        }
        $("#deliveryboy-edit-delete-button").click(
            function () {
                var confirmationMsg = $.mage.__("Are you sure you want to do this?");
                var deleteUrl = $("#deliveryboy-edit-delete-button").data("url");
                confirm({
                    "content": confirmationMsg,
                    "actions": {
                        confirm: function () {
                            getBannerForm(deleteUrl).appendTo("body").submit();
                        }
                    }
                });
                return false;
            }
        );
    }
);