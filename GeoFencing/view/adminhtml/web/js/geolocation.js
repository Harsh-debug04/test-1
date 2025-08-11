define([
    'jquery',
    'uiComponent',
    'Magento_Ui/js/lib/view/utils/async'
], function ($, Component, async) {
    'use strict';

    return Component.extend({
        defaults: {
            apiKey: '',
            inputSelector: 'input[name="product[geo_location]"]'
        },

        initialize: function () {
            this._super();
            var self = this;

            if (!this.apiKey) {
                console.error('AgriCart_GeoFencing: Google API Key is missing. Autocomplete is disabled.');
                return;
            }

            // This reliably waits for the input field to be rendered on the page.
            async(this.inputSelector, function (input) {
                // Load the Google Maps script only when the input is ready.
                require(['async!https://maps.googleapis.com/maps/api/js?key=' + self.apiKey + '&libraries=places'], function () {
                    self.initAutocomplete(input);
                });
            });
        },

        initAutocomplete: function (input) {
            var autocomplete = new google.maps.places.Autocomplete(input);
            autocomplete.setFields(['name', 'geometry']);
            var validated = false; // Flag to track if a valid place was selected.

            autocomplete.addListener('place_changed', function () {
                var place = autocomplete.getPlace();
                if (place.geometry && place.geometry.location) {
                    var loc = place.geometry.location;
                    // Format the value consistently: "Place Name (latitude, longitude)"
                    input.value = place.name + ' (' + loc.lat() + ', ' + loc.lng() + ')';
                    validated = true;
                    // Notify Magento's UI components of the change.
                    $(input).trigger('change');
                }
            });

            // If the user manually types in the field after selecting a place,
            // reset the validation flag.
            $(input).on('input', function () {
                if (validated) {
                    validated = false;
                }
            });
        }
    });
});
