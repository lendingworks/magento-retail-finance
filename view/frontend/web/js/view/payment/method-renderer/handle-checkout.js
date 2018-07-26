var orderToken = 'token_test_987hsdHhlk8ihk90Kd134s';
var completionHandler = function (id, status) {
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
};

$('body').delegate('#magento-checkout-button', 'click', function (e) {
    e.preventDefault();
    $(e.target).attr('disabled', 'disabled');

    LendingWorksCheckout(
        orderToken,
        window.location.href,
        completionHandler
    )();
});