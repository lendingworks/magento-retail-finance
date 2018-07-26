define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (
        Component,
        rendererList
    ) {
        'use strict';
        rendererList.push(
            {
                type: 'lendingworks_retailfinance',
                component: 'LendingWorks_RetailFinance/js/view/payment/method-renderer/rf-method'
            }
        );
        /** Add view logic here if needed */
        return Component.extend({});
    }
);