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
    "Webkul_DeliveryBoy/js/model/full-screen-loader",
    "mage/cookies",
    "googleMapKey",
    "domReady!",
], function (
    $,
    ko,
    Component,
    $t,
    fullScreenLoader
) {
    "use strict";

    return Component.extend({
        timeoutId: null,
        estTimeValHTMLElm: null,
        estTimeContHTMLElm: null,
        ajaxRequest: null,
        trackingDataUrl: null,
        directionsService: null,
        directionsDisplay: null,
        deliveryboyOrderId: null,
        mapElement: null,
        element: null,
        trackOrderErrorContElm: null,
        trackOrderErrorTextElm: null,
        orderTrackingDeliveryboyElm: null,
        canStopLoader: ko.observable(true),
        trackOrderButtonElm: null,

        calculateAndDisplayRoute: function (ori, dest) {
            this.directionsService.route(
                {
                    origin: {
                        lat: ori.lat,
                        lng: ori.lng,
                    },
                    destination: {
                        lat: dest.lat,
                        lng: dest.lng,
                    },
                    travelMode: "DRIVING",
                },
                function (response, status) {
                    if (status === "OK") {
                        this.directionsDisplay.setDirections(response);
                        var estimatedTime =
                            response.routes["0"].legs["0"].duration.text;
                        this.processSuccessView(estimatedTime);
                    }
                }.bind(this)
            );
        },

        processSuccessView: function (estimatedTime) {
            this.estTimeValHTMLElm.html(estimatedTime);
            this.orderTrackingDeliveryboy.removeClass("no_display");
            this.trackOrderErrorContElm.addClass("no_display");
        },

        processFailureView: function (message) {
            this.orderTrackingDeliveryboy.addClass("no_display");
            this.trackOrderErrorContElm.removeClass("no_display");
            this.trackOrderErrorTextElm.html(message);
            if (this.timeoutId) {
                window.clearTimeout(this.timeoutId);
            }
        },

        showDeliverPath: function () {
            if (this.ajaxRequest && this.ajaxRequest.readyState != 4) {
                this.ajaxRequest.abort();
            }
            var formKey = $.mage.cookies.get("form_key");
            this.ajaxRequest = jQuery
                .ajax({
                    url: this.trackingDataUrl,
                    data: {
                        deliveryboy_order_id: this.deliveryboyOrderId,
                        form_key: formKey,
                    },
                    context: this,
                    showLoader: false,
                    async: true,
                    type: "POST",
                })
                .done(
                    function (result) {
                        if (
                            result.hasOwnProperty("success") &&
                            result.success
                        ) {
                            this.calculateAndDisplayRoute(
                                {
                                    lat: parseFloat(result.db_lat),
                                    lng: parseFloat(result.db_lng),
                                },
                                {
                                    lat: parseFloat(result.customer_lat),
                                    lng: parseFloat(result.customer_lng),
                                }
                            );
                        } else {
                            this.processFailureView(
                                $.mage.__(
                                    "Unable to fetch tracking data. Please try again later."
                                )
                            );
                        }
                    }.bind(this)
                )
                .fail(
                    function () {
                        this.processFailureView(
                            $.mage.__(
                                "Unable to fetch tracking data. Please try again later."
                            )
                        );
                    }.bind(this)
                ).always( () => {
                    if(this.canStopLoader()) {
                        fullScreenLoader.stopLoader();
                        this.canStopLoader(false);
                    };
                });
        },

        /** @inheritdoc */
        initialize: function (config, element) {
            this._super();

            this.directionsService = new google.maps.DirectionsService();
            this.directionsDisplay = new google.maps.DirectionsRenderer();
            this.estTimeValHTMLElm = $(element).find(".estimated_time_value");
            this.estTimeContHTMLElm = $(element).find(".estimated_time");
            this.mapElement = $(element).find(".db_order_track");
            this.orderTrackingDeliveryboy = $(element).find(".order-tracking-delivery-boy");
            this.trackOrderErrorContElm = $(element).find(
                ".track-order-error-container"
            );
            this.trackOrderErrorTextElm = $(element).find(
                ".track-order-error-text"
            );
            this.trackingDataUrl = config.trackingDataUrl;
            this.deliveryboyOrderId = config.deliveryboyOrderId;
            this.element = element;

            fullScreenLoader.setContainerSelector(
                ".deliveryboy-track-order-wrapper"
            );

            this.trackOrderButtonElm = $(
                "#deliveryboy-track-order-loader_" + config.deliveryboyOrderId
            )
                .parents(".deliveryboy-track-order-wrapper")
                .first()
                .find(".action.track-order");
            this.trackOrderButtonElm.on('click', () => {
                $(element).modal('openModal');
            });

            var modal = $(element).modal({
                type: "popup",
                modalClass: "delivery-boy-track-order-model",
                responsive: true,
                innerScroll: true,
                title: $t("Track Order"),
                buttons: [
                    {
                        text: $.mage.__("Close"),
                        class: "",
                        click: function () {
                            this.closeModal();
                        },
                    },
                ],
                opened: () => {
                    this.canStopLoader(true);
                    fullScreenLoader.startLoader();
                    this.directionsDisplay = new google.maps.DirectionsRenderer(
                        {
                            polylineOptions: {
                                strokeColor: "#005bef",
                            },
                        }
                    );
                    var map = new google.maps.Map(this.mapElement[0], {
                        center: new google.maps.LatLng(28.646, 77.3695),
                        zoom: 10,
                    });
                    this.showDeliverPath();
                    this.directionsDisplay.setMap(map);

                    this.timeoutId = window.setInterval(() => {
                        this.showDeliverPath();
                    }, 10000);
                },
                closed: () => {
                    this.estTimeValHTMLElm.html("");
                    this.estTimeContHTMLElm.addClass("no_display");
                    this.trackOrderErrorContElm.addClass("no_display");
                    this.trackOrderErrorTextElm.html("");
                    if (this.timeoutId) {
                        window.clearTimeout(this.timeoutId);
                    }
                },
            });
        },
    });
});
