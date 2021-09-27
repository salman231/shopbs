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
define(
    [
        "jquery",
        "mage/calendar",
        'mage/translate',
        'googleMapKey',
        'Magento_Ui/js/modal/alert',
    ],
    function ($) {
        'use strict';

        $.widget(
            'mage.dashboarddeliveryboylocation', {
                options: {},
                _create: function () {
                    $("body .location").map(
                        function () {
                            var latitude = 0;
                            var longitude = 0;
                            var values = ($(this).val()).split("||");
                            if (values.length > 0 && values[0] != "" && values[1] != "") {
                                latitude = parseFloat(values[0]);
                                longitude = parseFloat(values[1]);
                                myLatlng = {
                                    lat: latitude,
                                    lng: longitude
                                };
                                map = new google.maps.Map(
                                    document.getElementById('map'), {
                                        mapTypeControl: false,
                                        center: myLatlng,
                                        zoom: 6
                                    }
                                );
                                marker = new google.maps.Marker({
                                    position: myLatlng,
                                    map: map,
                                    title: 'Click to zoom'
                                });
                            }
                        }
                    ).get();

                    // new AutocompleteDirectionsHandler(map);

                    marker.addListener(
                        'click',
                        function () {
                            map.setZoom(8);
                            map.setCenter(marker.getPosition());
                        }
                    );

                    var infowindow = new google.maps.InfoWindow();
                    var service = new google.maps.places.PlacesService(map);
                    service.getDetails({
                        placeId: 'ChIJN1t_tDeuEmsRUsoyG83frY4'
                    }, function (place, status) {
                        if (status === google.maps.places.PlacesServiceStatus.OK) {
                            var marker = new google.maps.Marker({
                                map: map,
                                position: place.geometry.location
                            });
                            google.maps.event.addListener(
                                marker, 'click',
                                function () {
                                    infowindow.setContent(
                                        '<div><strong>' + place.name + '</strong><br>' +
                                        'Place ID: ' + place.place_id + '<br>' +
                                        place.formatted_address + '</div>'
                                    );
                                    infowindow.open(map, this);
                                }
                            );
                        }
                    });
                }
            }
        );
        return $.mage.dashboarddeliveryboylocation;
    }
);