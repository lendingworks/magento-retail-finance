<?php

namespace LendingWorks\RetailFinance\Block\Adminhtml\Order\View;

use LendingWorks\RetailFinance\Helper\Data;
use Magento\Sales\Block\Adminhtml\Order\View\Tab\Info;

class LendingWorksData extends Info
{
  /**
   * @return string
   */
    public function getLWID()
    {
        return $this->getOrder()->getData(Data::ORDER_ID_ATTRIBUTE_KEY) ?: '-';
    }

  /**
   * @return string
   */
    public function getLWStatus()
    {
        return $this->getOrder()->getData(Data::ORDER_STATUS_ATTRIBUTE_KEY) ?: '-';
    }

  /**
   * @return string
   */
    public function getLWFulfilmentStatus()
    {
        return $this->getOrder()->getData(Data::ORDER_FULFILMENT_STATUS_ATTRIBUTE_KEY)
          ?: Data::ORDER_FULFILMENT_STATUS_UNFULFILLED;
    }

    public function canFulfill()
    {
        return $this->_scopeConfig->getValue('payment/' . Data::PAYMENT_CODE . '/allow_manual_fulfilment')
          && Data::isFulfillableStatus($this->getLWFulfilmentStatus());
    }
}
