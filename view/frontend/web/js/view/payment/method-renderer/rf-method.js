define(
    [
        'Magento_Payment/js/view/payment/iframe',
        'jquery',
        'Magento_Checkout/js/model/quote'
    ],
    function (Component, $, q) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'LendingWorks_RetailFinance/payment/retail-finance-form'
            },

            selectPaymentMethod: function () {
                $.ajax({
                    showLoader: true,
                    url: '/lwapi/payment/createorder',
                    data: { amount : this.grandTotal(), orderID: q.getOrderToken() },
                    type: "POST",
                    dataType: 'json'
                }).done(function (data) {
                    alert('Request Sent');
                }).fail(function (data) {
                    alert('Request Failed')
                });
                return this._super();
            },

            getCode: function() {
                return 'lendingworks_retailfinance';
            },

            grandTotal: function() {
                var totals = q.getTotals()();
                var segments = totals['total_segments'];
                return segments[segments.length - 1].value;
            },

            isActive: function() {
                return true;
            },

            validate: function() {
                var $form = $('#' + this.getCode() + '-form');
                return $form.validation() && $form.validation('isValid');
            },

            getOrderToken: function () {
                return '<?php echo $_SESSION["lw-rf-order-id"]; ?>';
            },

            placeOrder: function () {
                self = this;
                $.getScript('http://secure.docker:3000/checkout.js')
                    .done(function(script, textStatus) {
                        console.log(textStatus);
                        LendingWorksCheckout(
                            self.getOrderToken(),
                            window.location.href,
                            self.completionHandler()
                        )();
                    })
                    .fail(function (jqxhr, settings, exception) {
                        console.debug(jqxhr);
                        console.log(exception);
                        console.log('oh noes');
                    });
                console.log('ajax fired');
            },

            completionHandler: function (id, status) {
                console.log('my status is....');
                console.log(status);
                console.log('my id is....');
                console.log(id);
                if (status === 'cancelled') {
                    // Re-enable submit button.
                    return;
                }

                if (status === 'declined') {
                    // Re-enable submit button and display error message telling customer to
                    // pick a different payment method. Also prevent the Lending Works popup
                    // from being triggered again.
                    return;
                }

                if (['approved', 'referred'].indexOf(status) !== -1) {
                    // Trigger form submission.
                    // Update the form and set the 'id' and 'status' values.
                }
            },
        });
    }
);