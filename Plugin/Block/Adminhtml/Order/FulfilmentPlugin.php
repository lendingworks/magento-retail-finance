<?php

namespace LendingWorks\RetailFinance\Plugin\Block\Adminhtml\Order;

use LendingWorks\RetailFinance\Helper\Data;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Sales\Block\Adminhtml\Order\View;

class FulfilmentPlugin
{
  /**
   * @var ScopeConfigInterface
   */
    private $scopeConfig;

  /**
   * @param ScopeConfigInterface $scopeConfig
   */
    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

  /**
   * Checks for manual fulfilment config and creates a button for fulfilment if applicable
   * @param View $view
   */
    public function beforeSetLayout(View $view)
    {
        $order = $view->getOrder();
        $fulfillEnabled = $this->scopeConfig->getValue('payment/' . Data::PAYMENT_CODE . '/allow_manual_fulfilment');
        $isFulfillable = Data::isFulfillableStatus($order->getData( Data::ORDER_FULFILMENT_STATUS_ATTRIBUTE_KEY));
        if ($fulfillEnabled && $isFulfillable) {
            $message = 'Are you sure you want to fulfill this order?';
            $lwID = $order->getData(Data::ORDER_ID_ATTRIBUTE_KEY);
            $url = '/lwapi/order/fulfillorder?lw_id=' . $lwID;
            $view->addButton(
                'lendingworks_retailfinance_fulfilment',
                ['label' => __('Mark Order Fulfilled'), 'onclick' => "confirmSetLocation('{$message}', '{$url}')"],
                -1
            );
        }
    }
}
