define(
    [
        'Magento_Checkout/js/view/payment/default',
        'jquery',
        'Magento_Checkout/js/model/quote',
        'Magento_Ui/js/model/messageList'
    ],
    function (Component, $, q, messages) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'LendingWorks_RetailFinance/payment/retail-finance-form',
            },

            selectPaymentMethod: function () {
                self = this;
                $.ajax({
                    showLoader: true,
                    url: '/lwapi/payment/createorder',
                    type: "POST",
                    dataType: 'json'
                }).done(function (data) {
                    self.setOrderToken(data.token);
                    self.setScriptURL(data.script_url);
                    $('#lw-place-order').removeAttr('disabled');
                }).fail(function () {
                    messages.addErrorMessage({
                        message: 'Sorry - there has been an error connecting to Lending Works. Please contact the site administrator or try an alternative payment method.'
                    });
                    $('#lw-place-order').attr('disabled', 'disabled');
                });
                return this._super();
            },

            getCode: function () {
                return 'lendingworks_retailfinance';
            },

            grandTotal: function () {
                let totals = q.getTotals()();
                let segments = totals['total_segments'];
                return segments[segments.length - 1].value;
            },

            isActive: function () {
                return true;
            },

            validate: function () {
                let $form = $('#' + this.getCode() + '-form');
                return $form.validation() && $form.validation('isValid');
            },

            getOrderToken: function () {
                return $('#lw-place-order').data('lw-order-token');
            },

            setOrderToken: function (token) {
                $('#lw-place-order').data('lw-order-token', token);
            },

            getScriptURL: function () {
                return $('#lw-place-order').data('lw-script-url');
            },

            setScriptURL: function (url) {
                $('#lw-place-order').data('lw-script-url', url);
            },

            applyForFinance: function (data, event) {
                self = this;
                $.getScript(this.getScriptURL())
                    .done(function () {
                        LendingWorksCheckout(
                            self.getOrderToken(),
                            window.location.href,
                            function (status, id) {
                                if (status === 'cancelled') {
                                    $('#lw-place-order').removeAttr('disabled');
                                    messages.addErrorMessage({
                                        message: 'Your application has been cancelled. You may apply again if you wish.'
                                    });
                                    return;
                                }

                                if (status === 'declined') {
                                    messages.addErrorMessage({
                                        message: 'Sorry, your application has been declined. Please select another payment method.'});
                                    $('#lw-place-order').attr('disabled', 'disabled');
                                    return;
                                }

                                if (['approved', 'referred'].indexOf(status) !== -1) {
                                    self.setLWDetailsForQuote(status, id, data, event);
                                    return;
                                }
                            }
                        )()

                    })
                    .fail(function () {
                        messages.addErrorMessage({
                            message: 'Couldn\'t load Lending Works checkout - please contact your site administrator'
                        });
                        return;
                    });
            },

            setLWDetailsForQuote: function (lw_status, lw_id, data, event) {
                self = this;
                $.ajax({
                    showLoader: true,
                    url: '/lwapi/payment/addlworderdetails',
                    data: {
                        lw_order_id: lw_id,
                        lw_order_status: lw_status,
                    },
                    type: "POST",
                    dataType: 'json'
                }).fail(function () {
                    messages.addErrorMessage({
                        message: 'Sorry - there has been an error placing the order. Please contact the site administrator'
                    });
                }).done(function () {
                    self.placeOrder(data, event);
                });
            }

        });
    }
);