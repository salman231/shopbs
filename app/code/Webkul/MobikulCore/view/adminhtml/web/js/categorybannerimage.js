/**
 * Webkul Software.
 *
 *
 *
 * @category  Webkul
 * @package   Webkul_Mobikul
 * @author    Webkul <support@webkul.com>
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html ASL Licence
 * @link      https://store.webkul.com/license.html
 */
/*jshint jquery:true*/
define([
    "jquery",
    "mage/template",
    "mage/translate",
    "Magento_Ui/js/modal/alert",
    "jquery/ui",
], function ($, mageTemplate, $t, alert) {
    "use strict";
    $.widget("categorybannerimage.setattr", {
        _create: function () {
            var clicked = false;
            var attribute = this.options;
            var count = attribute.key;

            $("#container").on("click", "[data-index=mobikul-configuration]", function () {
                $("[data-index=ar_model_file_ios]").after($("#wk-mobikul-categorybannerimages").html());
                $("#wk-mobikul-categorybannerimages").remove();
                if (clicked == false) {
                    if (parseInt(attribute.arType) == 1) {
                        $("#container [data-index=ar_2d_file]").hide();
                        $(".categorybanner-image").show();
                    } else {
                        $("#container [data-index=ar_model_file_android]").hide();
                        $("#container [data-index=ar_model_file_ios]").hide();
                        $(".categorybanner-image").hide();
                    }
                }
                clicked = true;
            });

            $(document).on("click", "#wk-mobikul-categorybanner-add-more", function () {
                var progressTmpl = mageTemplate("#wk-categorybanner-template"), tmpl;
                tmpl = progressTmpl(
                    {
                        data: {
                            index: count,
                        }
                    }
                );
                $("#wk-mobikul-category-banner").after(tmpl);
                count++;
            });

            $("#container").on("click", ".wk-mobikul-categorybanner-delete", function () {
                $(this).parent().parent().remove();
            });

            $("#container").on("change", "input[name^='mobikul_categoryimages[banner]']", function () {
                var fileName = ($(this).val());
                var extArray = ["png", "jpg", "jpeg"];
                var extension = fileName.substr(fileName.lastIndexOf(".") + 1);
                if (jQuery.inArray(extension, extArray) === -1) {
                    alert({
                        content: $t("Wrong file type given in category banner image.")
                    });
                    $(this).val("");
                    $(this).focus();
                }
            });

            $('.admin__control-file').change(function(){
                $(this).siblings('input').remove();
            });
        }
    });
    return $.categorybannerimage.setattr;
});
