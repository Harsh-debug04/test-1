define([
    'jquery',
    'uiComponent'
], function ($, Component) {
    'use strict';

    return Component.extend({
        defaults: {
            apiKey: '',
            location: '',
            radius: 1000 // Default radius in meters
        },

        initialize: function (config, element) {
            this._super();
            this.element = element;
            this.apiKey = config.apiKey;
            this.location = config.location;
            this.radius = config.radius;

            if (!this.apiKey) {
                // Don't attempt to load the map if the API key is not configured.
                return;
            }

            // Dynamically load the Google Maps script using the API key from the component's configuration.
            require(['async!https://maps.googleapis.com/maps/api/js?key=' + this.apiKey], function () {
                // Once the script is loaded, initialize the map.
                this.initMap();
            }.bind(this));
        },

        initMap: function () {
            var latLng = this.parseLocation(this.location);

            if (!latLng) {
                console.error('AgriCart_GeoFencing: Invalid location format. Could not parse coordinates.');
                return;
            }

            var map = new google.maps.Map(this.element, {
                zoom: 12,
                center: latLng,
                mapTypeId: 'roadmap'
            });

            // Add a marker for the exact location
            new google.maps.Marker({
                position: latLng,
                map: map,
                title: 'Product Location'
            });

            // Add a circle to represent the geofence
            new google.maps.Circle({
                strokeColor: '#FF0000',
                strokeOpacity: 0.8,
                strokeWeight: 2,
                fillColor: '#FF0000',
                fillOpacity: 0.35,
                map: map,
                center: latLng,
                radius: this.radius
            });
        },

        /**
         * Parses a location string like "New York, NY, USA (40.7127753, -74.0059728)"
         * to extract the latitude and longitude.
         * @param {string} locationString
         * @returns {object|null} - An object with lat and lng properties, or null if parsing fails.
         */
        parseLocation: function (locationString) {
            // Regex to find content within the last parentheses
            var matches = locationString.match(/\(([^)]+)\)$/);
            if (matches && matches[1]) {
                var parts = matches[1].split(',');
                if (parts.length === 2) {
                    var lat = parseFloat(parts[0].trim());
                    var lng = parseFloat(parts[1].trim());

                    if (!isNaN(lat) && !isNaN(lng)) {
                        return { lat: lat, lng: lng };
                    }
                }
            }
            return null; // Return null if parsing fails
        }
    });
});
