<?php

namespace LendingWorks\RetailFinance\Observer;

use LendingWorks\RetailFinance\Helper\Data;
use Magento\Framework\DataObject\Copy;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Model\Quote;
use Magento\Sales\Model\Order;

class SalesEventQuoteToOrderObserver implements ObserverInterface
{
  /**
   * @var Copy
   */
    private $objectCopyService;

  /**
   * @param Copy $objectCopyService
   */
    public function __construct(Copy $objectCopyService)
    {
        $this->objectCopyService = $objectCopyService;
    }

  /**
   *
   * @param Observer $observer
   * @return $this
   */
    public function execute(Observer $observer)
    {
        /** @var Quote $quote */
        $quote = $observer->getEvent()->getData('quote');

        if ($quote->getPayment()->getMethod() !== Data::PAYMENT_CODE) {
            return $this;
        }

        $lendingWorksID = $quote->getData(Data::ORDER_ID_ATTRIBUTE_KEY);

        if ($lendingWorksID === null || $lendingWorksID === '') {
            throw new \RuntimeException('Lending Works ID not found for Retail Finance order');
        }

        /** @var Order $order */
        $order = $observer->getEvent()->getData('order');

        $this->objectCopyService->copyFieldsetToTarget('sales_convert_quote', 'to_order', $quote, $order);
        return $this;
    }
}
