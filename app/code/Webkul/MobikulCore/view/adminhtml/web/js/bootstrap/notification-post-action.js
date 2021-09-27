require([
    "jquery",
    "Magento_Ui/js/modal/confirm",
    "Magento_Ui/js/modal/alert",
    "uiRegistry",
    "mage/translate"
], function ($, confirm, alert, uiRegistry) {
        "use strict";
        function getNotificationForm(url)
        {
            return $("<form>", {"action":url, "method":"POST"}).append($("<input>", {"name":"form_key", "value":window.FORM_KEY, "type":"hidden"}));
        }

        $("#notification-edit-delete-button").on("click", function () {
            var confirmationMsg = $.mage.__("Are you sure you want to do this?");
            var deleteUrl = $("#notification-edit-delete-button").data("url");
            confirm({
                "content": confirmationMsg,
                "actions":  {
                    confirm: function () {
                        getNotificationForm(deleteUrl).appendTo("body").submit();
                    }
                }
            });
            return false;
        });

        var counter = 0;
        var changeTriggerInterval = false;
        $(window).bind("load", function () {
            changeTriggerInterval = setInterval(function () {
                $("body").find("select[name='mobikul_notification[type]']").trigger("change");
            }, 1000);
        });

        $("body").delegate("select[name='mobikul_notification[type]']", "change", function () {
            var target = $(this).parents(".admin__field").next(".admin__field").find("input");
            var field = $(this).parents(".admin__field").next(".admin__field");
            var fieldIndex = field.attr('data-index');
            var proCatId = uiRegistry.get('index = '+fieldIndex);
            if($(this).val() == "others"){
                if (proCatId.required()) {
                    proCatId.setValidation('required-entry', false)
                }
                target.attr("disabled", "disabled");
            }else if ($(this).val() == "custom") {
                if (proCatId.required()) {
                    proCatId.setValidation('required-entry', false)
                }
                target.attr("disabled", "disabled");
            } else {
                if (!proCatId.required()) {
                    proCatId.setValidation('required-entry', true)
                }
                target.removeAttr("disabled");
            }
            if (counter <= 5) {
                counter++;
            } else {
                clearInterval(changeTriggerInterval);
            }
        });
    });