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
/*jshint jquery:true*/
define(
    [
        'jquery',
        'googleMapKey'
    ],
    function ($) {
        'use strict';
        $.widget(
            'mage.trackdeliveryboy', {
                _create: function () {
                    var self = this;
                    var estimatedTime;
                    var directionsService = new google.maps.DirectionsService();
                    var directionsDisplay = new google.maps.DirectionsRenderer();

                    window.setInterval(function () {
                        showDeliverPath();
                    }, 10000);

                    function calculateAndDisplayRoute(
                        directionsService,
                        directionsDisplay,
                        ori,
                        dest
                    ) {
                        directionsService.route(
                            {
                                origin: {
                                    lat: ori.lat,
                                    lng: ori.lng,
                                },
                                destination: {
                                    lat: parseFloat(dest.lat),
                                    lng: parseFloat(dest.lng),
                                },
                                travelMode: "DRIVING",
                            },
                            function (response, status) {
                                if (status === "OK") {
                                    directionsDisplay.setDirections(response);
                                    estimatedTime =
                                        response.routes["0"].legs["0"].duration
                                            .text;
                                    $("#estimated_time_value").html(
                                        estimatedTime
                                    );
                                    $(".estimated_time").removeClass(
                                        "no_display"
                                    );
                                }
                            }
                        );
                    }

                    /**
                     * Function to display path of delivery boy to customer
                     */
                    function showDeliverPath() {
                        if (ajaxRequest && ajaxRequest.readyState != 4) {
                            ajaxRequest.abort();
                        }
                        var formKey = $('input[name="form_key"]').val();
                        var action = self.options.action;
                        var ajaxRequest = jQuery.ajax({
                            url: action,
                            data: {
                                orderId: self.options.orderId,
                                form_key: formKey,
                            },
                            showLoader: false,
                            async: true,
                            type: "POST",
                            success: function (result) {
                                if (result.success) {
                                    calculateAndDisplayRoute(
                                        directionsService,
                                        directionsDisplay,
                                        {
                                            lat: parseFloat(result.db_lat),
                                            lng: parseFloat(result.db_lng),
                                        },
                                        {
                                            lat: result.customer_lat,
                                            lng: result.customer_lng,
                                        }
                                    );
                                } else {
                                    window.alert(result.message);
                                }
                            },
                        });
                    }

                    /**
                     * Function to initialize the map
                     */
                    $(document).ready(function () {
                        directionsDisplay = new google.maps.DirectionsRenderer({
                            polylineOptions: {
                                strokeColor: "#005bef",
                            },
                        });
                        var map = new google.maps.Map(
                            document.getElementById("db_order_track"),
                            {
                                center: new google.maps.LatLng(28.646, 77.3695),
                                zoom: 10,
                            }
                        );
                        showDeliverPath();
                        directionsDisplay.setMap(map);
                    });
                }
            }
        );
        return $.mage.trackdeliveryboy;
    }
);