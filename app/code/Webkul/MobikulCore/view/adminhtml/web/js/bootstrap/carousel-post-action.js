require([
    "jquery",
    "Magento_Ui/js/modal/confirm",
    "mage/translate"
    ], function ($, confirm) {
        "use strict";
        function getCarouselForm(url)
        {
            return $("<form>", {"action":url, "method":"POST"})
            .append($("<input>", {
                    "name": "form_key",
                    "value": window.FORM_KEY,
                    "type": "hidden"
                }));
        }
        
        $("#carousel-edit-delete-button").click(function () {
            var confirmationMsg = $.mage.__("Are you sure you want to do this?");
            var deleteUrl = $("#carousel-edit-delete-button").data("url");
            confirm({
                "content": confirmationMsg,
                "actions": {
                    confirm: function () {
                        getCarouselForm(deleteUrl).appendTo("body").submit();
                    }
                }
            });
            return false;
        });
    });