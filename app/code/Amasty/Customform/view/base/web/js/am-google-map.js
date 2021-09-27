define([
    "jquery",
    'jquery/ui'
], function ($, ui) {
    'use strict';

    $.widget('mage.amGoogleMap', {
        options: {
            defaultPosition: {
                lat: 0,
                lng: 0
            },
            position: null,
            enabledMarker: true,
            showFormattedAddress: false,
            styles: null
        },
        zoom: 1,
        map: null,

        _create: function() {
            this.mapInput = this.element.find('input').hide();
            if (typeof google != 'undefined' && google.maps) {
                if (!this.options.position) {
                    this.options.position = this.options.defaultPosition;
                }
                if (this.options.zoom) {
                    this.zoom = parseInt(this.options.zoom);
                }

                if (this.element.find('.map').length == 0) {
                    this.element.prepend($('<div>',
                        {
                            'class': 'map'
                        }
                    ));
                }
                var map = new google.maps.Map(
                    this.element.find('.map')[0], {zoom: this.zoom, center: this.options.position}
                );
                this.map = map;
                if (this.options.styles) {
                    var previousStyles = $(this.map.getDiv()).attr('style');
                    if (!previousStyles) {
                        previousStyles = '';
                    }
                    $(this.map.getDiv()).attr(
                        'style',
                        previousStyles + ' ' + this.options.styles
                    );
                }
                this.mapInput.val(this.options.position);

                this.currentMarker = new google.maps.Marker({position: this.options.position, map: map});

                if (this.options.enabledMarker) {
                    google.maps.event.addListener(map, 'click', function (event) {
                        this.moveMarker(event.latLng);
                    }.bind(this));
                }

                this.map.addListener('zoom_changed', function() {
                    this.element.find('input.amform-googlemap').trigger('change');
                }.bind(this));

                if (this.options.showFormattedAddress) {
                    this.showAddress();
                }

                var searchbox = this.searchBox();

                google.maps.event.addListenerOnce(this.map, 'tilesloaded', function() {
                    searchbox.show();
                });
            }
        },

        getPosition: function () {
            return this.currentMarker.getPosition();
        },

        searchBox: function () {
            this.element.prepend($('<input>',
                {
                    'class': 'searchbox'
                }
            ).hide());

            var self = this,
                input = this.element.find('.searchbox'),
                searchBox = new google.maps.places.SearchBox(input[0]);
            this.map.controls[google.maps.ControlPosition.LEFT_BOTTOM].push(input[0]);

            // disable submit from on search
            input.onkeypress = function(e) {
                var key = e.charCode || e.keyCode || 0;
                if (key == 13) {
                    e.preventDefault();
                }
            };
            searchBox.addListener('places_changed', function (event) {
                var places = searchBox.getPlaces();

                if (places.length == 0) {
                    return;
                }

                var bounds = new google.maps.LatLngBounds();
                places.forEach(function (place) {
                    if (!place.geometry) {
                        return;
                    }

                    if (self.options.enabledMarker) {
                        self.moveMarker(place.geometry.location);
                    }

                    if (place.geometry.viewport) {
                        bounds.union(place.geometry.viewport);
                    } else {
                        bounds.extend(place.geometry.location);
                    }
                });
                self.map.fitBounds(bounds);
            });

            return input;
        },

        moveMarker: function (location) {
            if (this.currentMarker) {
                this.currentMarker.setMap(null);
            }
            this.currentMarker = new google.maps.Marker({
                position: location,
                map: this.map
            });
            this.mapInput.val(location);
            this.element.find('input.amform-googlemap').trigger('change');
        },

        showAddress: function () {
            if (this.element.find('.am-address').length == 0) {
                this.element.prepend($('<div>',
                    {
                        'class': 'am-address'
                    }
                ));
            }
            var geocoder = new google.maps.Geocoder(),
                addressField = this.element.find('.am-address'),
                position = this.getPosition();

            geocoder.geocode({
                'latLng': position
            }, function(results, status) {
                if (status == google.maps.GeocoderStatus.OK) {
                    if (results[0]) {
                        addressField.html(results[0].formatted_address);
                    }
                } else {
                    addressField.html(position.toString());
                }
            });
        },

        getZoom: function () {
            return this.map.getZoom();
        }
    });

    return $.mage.amGoogleMap;
});
