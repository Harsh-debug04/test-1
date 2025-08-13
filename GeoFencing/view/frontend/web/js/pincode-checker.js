define([
    'jquery',
    'uiComponent',
    'mage/url'
], function ($, Component, url) {
    'use strict';

    return Component.extend({
        defaults: {
            checkUrl: ''
        },

        initialize: function (config, element) {
            this._super();
            this.element = $(element);
            this.checkUrl = config.checkUrl;
            this.productId = this.element.data('product-id');
            this.pincodeField = this.element.find('#pincode');
            this.checkButton = this.element.find('#pincode-check-button');
            this.resultDiv = this.element.find('#pincode-check-result');
            this.addToCartButton = $('#product-addtocart-button');

            this.checkButton.on('click', $.proxy(this.checkPincode, this));
        },

        checkPincode: function () {
            var pincode = this.pincodeField.val();

            if (!pincode) {
                this.resultDiv.text('Please enter a pincode.').css('color', 'red');
                return;
            }

            this.showLoading(true);

            $.ajax({
                url: this.checkUrl,
                type: 'POST',
                dataType: 'json',
                data: {
                    pincode: pincode,
                    product_id: this.productId
                },
                success: $.proxy(this.handleResponse, this),
                error: $.proxy(this.handleError, this)
            });
        },

        handleResponse: function (response) {
            this.showLoading(false);

            if (response.success) {
                this.resultDiv.text(response.message).css('color', 'green');
                this.addToCartButton.prop('disabled', false).removeClass('disabled');
            } else {
                this.resultDiv.text(response.message).css('color', 'red');
                this.addToCartButton.prop('disabled', true).addClass('disabled');
            }
        },

        handleError: function () {
            this.showLoading(false);
            this.resultDiv.text('An error occurred while checking the pincode.').css('color', 'red');
            this.addToCartButton.prop('disabled', true).addClass('disabled');
        },

        showLoading: function (isLoading) {
            if (isLoading) {
                this.checkButton.prop('disabled', true).addClass('disabled');
                this.resultDiv.text('Checking...').css('color', 'orange');
            } else {
                this.checkButton.prop('disabled', false).removeClass('disabled');
            }
        }
    });
});
