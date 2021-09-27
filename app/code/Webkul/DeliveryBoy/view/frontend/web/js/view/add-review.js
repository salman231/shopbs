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
define([
    "jquery",
    "ko",
    "uiComponent",
    "mage/translate",
    "Webkul_DeliveryBoy/js/model/modal-messages",
    "Webkul_DeliveryBoy/js/model/global-messages",
    "Webkul_DeliveryBoy/js/model/full-screen-loader",
    "mage/cookies",
], function (
    $,
    ko,
    Component,
    $t,
    messageContainer,
    globalMessageContainer,
    fullScreenLoader
) {
    "use strict";

    return Component.extend({

        addReviewButton: null,

        /** @inheritdoc */
        initialize: function (config, element) {
            this._super();
            var shouldRemoveReviewWrapper = ko.observable(false);
            shouldRemoveReviewWrapper.subscribe((newValue) => {
                if (newValue) {
                        this.addReviewButton
                        .parents(".deliveryboy-review-wrapper")
                        .first()
                        .remove();
                }
            });


            var modal = $(element).modal({
                type: "popup",
                modalClass: "delivery-boy-add-review-model",
                responsive: true,
                innerScroll: true,
                title: $t("Add Review"),
                buttons: [
                    {
                        text: $t("Submit"),
                        class: "deliveryboy-review-form-submit-action",
                        click: function (event) {
                            event.preventDefault();
                            $(element).find("form").trigger("submit");
                        },
                    },
                ],
            });

            this.addReviewButton = $("#deliveryboy-review_" + config.deliveryboyOrderId)
                .parents(".deliveryboy-review-wrapper")
                .first()
                .find(".action.add-review");

            this.addReviewButton.on("click", () => {
                $(element).modal("openModal");
            });
            $(element)
                .find(".deliveryboy-review-form")
                .on("submit", function (event) {
                    event.preventDefault();

                    var reviewFormData = {},
                        reviewForm = event.target,
                        reviewFormDataArray = $(reviewForm).serializeArray();

                    reviewFormDataArray.forEach(function (entry) {
                        reviewFormData[entry.name] = entry.value;
                    });

                    reviewFormData["form_key"] = $.mage.cookies.get("form_key");

                    if (
                        $(reviewForm).validation() &&
                        $(reviewForm).validation("isValid")
                    ) {
                        fullScreenLoader.startLoader();
                        $.ajax({
                            type: "POST",
                            url: $(reviewForm).attr("action"),
                            global: true,
                            showLoader: true,
                            data: reviewFormData,
                        })
                            .done(function (response) {
                                fullScreenLoader.stopLoader();
                                if (response.hasOwnProperty("error")) {
                                    if (response.error) {
                                        messageContainer.addErrorMessage({
                                            message: response.message,
                                        });
                                        $(".delivery-boy-add-review-model")
                                            .find(".modal-content")
                                            .animate(
                                                {
                                                    scrollTop: 0,
                                                },
                                                300
                                            );
                                    } else {
                                        modal.modal("closeModal");
                                        setTimeout(function () {
                                            globalMessageContainer.addSuccessMessage(
                                                {
                                                    message: response.message,
                                                }
                                            );
                                            shouldRemoveReviewWrapper(true);
                                            $("html, body").animate(
                                                {
                                                    scrollTop: 0,
                                                },
                                                300
                                            );
                                        }, 1000);
                                    }
                                }
                            })
                            .fail(function () {
                                fullScreenLoader.stopLoader();
                                messageContainer.addErrorMessage({
                                    message: $t(
                                        "Could not add review. Please try again later"
                                    ),
                                });
                            });
                    }
                });
        },
    });
});
