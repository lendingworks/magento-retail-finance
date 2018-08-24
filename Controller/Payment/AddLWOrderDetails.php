<?php

namespace LendingWorks\RetailFinance\Controller\Payment;

use LendingWorks\RetailFinance\Controller\BaseAPIHandler;
use LendingWorks\RetailFinance\Helper\Data;
use Magento\Framework\App\ResponseInterface;

class AddLWOrderDetails extends BaseAPIHandler
{
  /**
   * Execute action based on request and return result
   *
   * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface
   * @throws \Magento\Framework\Exception\NotFoundException
   */
    public function execute()
    {
        parent::execute();
        $postData = $this->getRequest()->getPostValue();

        if (!isset($postData['lw_order_id'])) {
            return $this->result(400, 'Missing lw_order_id POST data');
        }

        if (!isset($postData['lw_order_status']) || !Data::isValidStatus($postData['lw_order_status'])) {
            return $this->result(400, 'Invalid lw_order_status received: ' . $postData['lw_order_status']);
        }

        $quote = $this->checkoutSession->getQuote();
        $quote->setData(Data::ORDER_ID_ATTRIBUTE_KEY, $postData['lw_order_id']);
        $quote->setData(Data::ORDER_STATUS_ATTRIBUTE_KEY, $postData['lw_order_status']);
        $quote->setData(Data::ORDER_FULFILMENT_STATUS_ATTRIBUTE_KEY, Data::ORDER_FULFILMENT_STATUS_UNFULFILLED);
        $quote->save();

        return $this->result(200, 'Application successful!', ['quote_id' => $quote->getId()]);
    }
}
