require([
    "jquery",
    "Magento_Ui/js/modal/confirm",
    "mage/translate"
    ], function ($, confirm) {
        "use strict";

        function getFeaturedcategoriesForm(url)
        {
            return $("<form>", {"action":url, "method":"POST"}).append($("<input>", {"name":"form_key", "value":window.FORM_KEY, "type":"hidden"}));
        }

        $("#featuredcategories-edit-delete-button").on("click", function () {
            var confirmationMsg = $.mage.__("Are you sure you want to do this?");
            var deleteUrl = $("#featuredcategories-edit-delete-button").data("url");
            confirm({
                "content": confirmationMsg,
                "actions":  {
                    confirm: function () {
                        getFeaturedcategoriesForm(deleteUrl).appendTo("body").submit();
                    }
                }
            });
            return false;
        });
    });