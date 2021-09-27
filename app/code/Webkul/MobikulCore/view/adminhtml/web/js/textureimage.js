/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MobikulCore
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
    $.widget("textureimage.setattr", {
        _create: function () {
            var attribute = this.options;
            var clicked = false;
            var count = attribute.key;

            $("#container").on("click", "[data-index=mobikul-configuration]", function () {
                $("[data-index=ar_model_file_ios]").after($("#wk-mobikul-textureimages").html());
                $("#wk-mobikul-textureimages").remove();
                if (clicked == false) {
                    if (parseInt(attribute.arType) == 1) {
                        $("#container [data-index=ar_2d_file]").hide();
                        $(".texture-image").show();
                    } else {
                        $("#container [data-index=ar_model_file_android]").hide();
                        $("#container [data-index=ar_model_file_ios]").hide();
                        $(".texture-image").hide();
                    }
                    setTimeout(function() {
                        $("#container #ar_model_file_android_model").text(attribute.ar_model_file_android_model);
                        $("#container #ar_model_file_ios_model").text(attribute.ar_model_file_ios_model);
                    }, 1000);
                }
                clicked = true;
            });
            
            $("#container").on("change", "select[name='product[ar_type]']", function () {
                if ($(this).val() == 1) {
                    $("[data-index=ar_2d_file]").hide();
                    $("[data-index=ar_model_file_android]").show();
                    $("[data-index=ar_model_file_ios]").show();
                    $(".texture-image").show();
                    setTimeout(function() {
                        $("#container #ar_model_file_android_model").text(attribute.ar_model_file_android_model);
                        $("#container #ar_model_file_ios_model").text(attribute.ar_model_file_ios_model);
                    }, 1000);
                } else {
                    $("[data-index=ar_2d_file]").show();
                    $("[data-index=ar_model_file_android]").hide();
                    $("[data-index=ar_model_file_ios]").hide();
                    $(".texture-image").hide();
                }
            });

            $("#container").on("click", "#wk-mobikul-texture-add-more", function () {
                var progressTmpl = mageTemplate("#wk-texture-template"),tmpl;
                tmpl = progressTmpl({
                        data: {
                            index: count,
                        }
                    }
                );
                $(".texture-image").last().after(tmpl);
                count++;
            });

            $("#container").on("click", "#wk-mobikul-texture-delete", function () {
                $(this).parents(".admin__field").remove();
            });

            $("#container").on("change", "input[name='product[ar_model_file_android]']", function () {
                var fileName = ($(this).val());
                var extension = fileName.substr(fileName.lastIndexOf(".") + 1);
                if (extension != "sfb") {
                    alert({
                        content: $t("Ar model file for android must have sfb extension.")
                    });
                    $(this).val("");
                    $(this).focus();
                }
            });
            $("#container").on("change", "input[name='product[ar_model_file_ios]']", function () {
                var fileName = ($(this).val());
                var extension = fileName.substr(fileName.lastIndexOf(".") + 1);
                if (extension != "usdz") {
                    alert({
                        content: $t("Ar model file for iOS must have usdz extension.")
                    });
                    $(this).val("");
                    $(this).focus();
                }
            });

            $("#container").on("change", "input[name='product[ar_2d_file]']", function () {
                var fileName = ($(this).val());
                var extension = fileName.substr(fileName.lastIndexOf(".") + 1);
                if (extension != "png") {
                    alert({
                        content: $t("Ar 2D model file must have png extension.")
                    });
                    $(this).val("");
                    $(this).focus();
                }
            });

            $("#container").on("change", "input[name^='ar_texture_image']", function () {
                var fileName = ($(this).val());
                var extension = fileName.substr(fileName.lastIndexOf(".") + 1);
                var extArray = ["png", "jpg", "jpeg"];
                if (jQuery.inArray(extension, extArray) === -1) {
                    alert({
                        content: $t("Wrong file type given in texture image.")
                    });
                    $(this).val("");
                    $(this).focus();
                }
            });
        }
    });
    return $.textureimage.setattr;
});
