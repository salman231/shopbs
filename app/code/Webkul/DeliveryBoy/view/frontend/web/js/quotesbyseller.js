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
        'mage/translate',
        'Magento_Ui/js/modal/alert',
        'googleMapKey'
    ],
    function ($, $t, alert) {
        'use strict';
        $.widget(
            'mage.quotesbyseller', {
                _create: function () {
                    var self = this;
                    var menu = self.options.menu;
                    var customerAddress = self.options.customerAddress;
                    var customerCoordinates = self.options.customerCoordinates;
                    var sellerCoordinates = self.options.sellerCoordinates;
                    var modalPopup;
                    var ajaxRequest;
                    var geocoder = new google.maps.Geocoder();

                    /**
                     * Function for event on cick of Table Row
                     */
                    $('.wk_table.wk_row').on(
                        'click',
                        function () {
                            $('.wk_row').removeClass('wk_viewed');
                            $(this).addClass('wk_viewed');
                        }
                    )

                    /**
                     * Function for click event on toggle button
                     */
                    $(document).on(
                        'click', '#map_view_btn',
                        function () {
                            $(this).attr('disabled', true);
                            $("#grid_view_btn").removeAttr('disabled');
                        }
                    );

                    /**
                     * Function for click event on toggle button
                     */
                    $(document).on(
                        'click', '#grid_view_btn',
                        function () {
                            $(this).attr('disabled', true);
                            $("#map_view_btn").removeAttr('disabled');
                        }
                    );

                    $(document).on(
                        'click', '.wk_rfq_container .data-row',
                        function () {
                            var sellerResponseId = $(this).find('td.response-id').text();
                            openPopup(sellerResponseId);
                        }
                    )

                    $(document).on(
                        'click', '.wk_seller_response .wk_row',
                        function () {
                            $('.row-details').hide();
                            $('.wk_row').removeClass('wk_viewed');
                            $(this).addClass('wk_viewed');
                            var rowId = $(this).attr('data-rowId');
                            initMap(rowId);
                            $("#details-" + rowId).show();
                        }
                    )

                    // function to open modal////////
                    function openPopup(sellerResponseId) {
                        modalPopup = $("#customer_popup_id").modal({
                            buttons: getButtons(sellerResponseId),
                            modalClass: 'quote_response_customer',
                            clickableOverlay: false,
                            type: 'popup',
                            title: 'Do you want to accept the quote?',
                        });
                        modalPopup.modal('openModal');
                    }

                    // function to manage Buttons modal response buttons /////////////////////////////////////
                    function getButtons(sellerResponseId) {
                        var buttons = [{
                                text: $t("Accept"),
                                class: 'customer_quote_accept',
                                click: function () {
                                    acceptQuote(sellerResponseId);
                                }
                            },
                            {
                                text: $t("Reject Quote"),
                                class: 'customer_quote_reject',
                                click: function () {
                                    rejectQuote(sellerResponseId)
                                }
                            }
                        ];
                        return buttons;
                    }

                    $(document).on(
                        'click', '.wk_accept_quote',
                        function () {
                            var id = $(this).attr('data-responseId');
                            acceptQuote(id);
                        }
                    );

                    $(document).on(
                        'click', '.wk_reject_quote',
                        function () {
                            var id = $(this).attr('data-responseId');
                            rejectQuote(id);
                        }
                    );

                    /**
                     * Ajax request to Accept quote
                     * 
                     * @param integer sellerResponseId 
                     */
                    function acceptQuote(sellerResponseId) {
                        $('.admin__data-grid-loading-mask').show();
                        if (ajaxRequest && ajaxRequest.readyState != 4) {
                            ajaxRequest.abort();
                        }
                        var formKey = $('input[name="form_key"]').val();
                        var quoteId = self.options.quote_id;
                        var acceptUrl = self.options.acceptUrl;
                        var ajaxRequest = jQuery.ajax({
                            url: acceptUrl,
                            data: {
                                'quote_id': quoteId,
                                'seller_response_id': sellerResponseId,
                                'form_key': formKey
                            },
                            showLoader: true,
                            async: true,
                            type: 'POST',
                            success: function (result) {
                                var checkoutUrl = self.options.baseUrl + "checkout/cart";
                                var backUrl = self.options.baseUrl + "customwork/customer/quotesbyseller/quote_id/" + quoteId;
                                if (!result.error) {
                                    window.location.replace(checkoutUrl);
                                } else {
                                    window.location.replace(backUrl);
                                }
                            }
                        });
                    }

                    /**
                     * Ajax Request to reject Quote
                     *
                     * @param integer sellerResponseId
                     */
                    function rejectQuote(sellerResponseId) {
                        if (ajaxRequest && ajaxRequest.readyState != 4) {
                            ajaxRequest.abort();
                        }
                        var formKey = $('input[name="form_key"]').val();
                        var quoteId = self.options.quote_id;
                        var rejectUrl = self.options.rejectUrl;
                        var ajaxRequest = jQuery.ajax({
                            url: rejectUrl,
                            data: {
                                'quote_id': quoteId,
                                'seller_response_id': sellerResponseId,
                                'form_key': formKey
                            },
                            showLoader: true,
                            async: true,
                            type: 'POST',
                            success: function (result) {
                                var returnUrl = self.options.baseUrl + "requestforquote/account/lists";
                                // modalPopup.modal('closeModal');
                                // if (!result.error) {
                                //     window.location.replace(returnUrl);
                                // }
                            }
                        });
                    }

                    function initMap(id) {
                        var directionsService = new google.maps.DirectionsService;
                        var directionsDisplay = new google.maps.DirectionsRenderer;
                        var map = new google.maps.Map(
                            document.getElementById('map-' + id), {
                                zoom: 10,
                                center: {
                                    lat: 23.885942,
                                    lng: 45.079162
                                },
                                image: "https://omega.io/vardangoyal.design057/magento-marketplace-rfq/raw/master/layout/customer/customer-requested-quote-sellers-map.jpg"
                            }
                        );
                        directionsDisplay.setMap(map);
                        var marker = new google.maps.Marker({
                            position: customerCoordinates,
                            map: map,
                            title: 'Uluru (Ayers Rock)'
                        });
                        calculateAndDisplayRoute(directionsService, directionsDisplay, customerCoordinates, sellerCoordinates[id]);
                    }

                    function calculateAndDisplayRoute(directionsService, directionsDisplay, ori, dest) {
                        directionsService.route({
                            origin: {
                                lat: ori.lat,
                                lng: ori.lng
                            },
                            destination: {
                                lat: parseFloat(dest.lat),
                                lng: parseFloat(dest.lng)
                            },
                            travelMode: 'DRIVING'
                        }, function (response, status) {
                            if (status === 'OK') {
                                directionsDisplay.setDirections(response);
                            } else {
                                window.alert($t("Directions not available"));
                            }
                        });
                    }

                    function getInitials(string) {
                        var names = string.split(' '),
                            initials = names[0].substring(0, 1).toUpperCase();

                        if (names.length > 1) {
                            initials += names[names.length - 1].substring(0, 1).toUpperCase();
                        }
                        return initials;
                    };

                    $(document).ready(
                        function () {
                            var directionsService = new google.maps.DirectionsService;
                            // var directionsDisplay = new google.maps.DirectionsRenderer;
                            var directionsDisplay = new google.maps.DirectionsRenderer({
                                polylineOptions: {
                                    strokeColor: "#005bef"
                                }
                            });
                            var map = new google.maps.Map(
                                document.getElementById('map_view_map'), {
                                    zoom: 10,
                                    center: self.options.customerCoordinates
                                }
                            );
                            var marker = new google.maps.Marker({
                                position: self.options.customerCoordinates,
                                map: map,
                                title: $t("You are here")
                            });

                            directionsDisplay.setMap(map);
                            $.each(
                                self.options.sellerCoordinates,
                                function (key, val) {
                                    var contentString = "<div><img src='" + val.image + "' style='width:30px;height:30px'/><span style='color:#006bb4;'><b>" + val.name + "</b></span><br><span><b>Price: </b>" + val.price + "</span><br><span><b>Delivery: </b>" + val.delivery + "</span></div>";
                                    var infowindow = new google.maps.InfoWindow({
                                        content: contentString
                                    });
                                    var sellerLoc = {
                                        lat: parseFloat(val.lat),
                                        lng: parseFloat(val.lng)
                                    };
                                    var marker = new google.maps.Marker({
                                        position: sellerLoc,
                                        map: map,
                                        label: getInitials(val.name),
                                        title: val.name
                                    });
                                    marker.set("id", val.id);
                                    marker.addListener(
                                        'click',
                                        function () {
                                            var id = marker.get('id');
                                            $('.wk_response_options_map').addClass('wk_do_not_show');
                                            $('#response-' + id).removeClass('wk_do_not_show');
                                            var elmnt = document.getElementById('response-' + id);
                                            elmnt.scrollIntoView()
                                            calculateAndDisplayRoute(directionsService, directionsDisplay, customerCoordinates, sellerLoc);
                                            infowindow.open(map, marker);
                                        }
                                    );
                                }
                            )
                        }
                    );
                }
            }
        );
        return $.mage.quotesbyseller;
    }
);