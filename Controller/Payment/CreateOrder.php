<?php

namespace LendingWorks\RetailFinance\Controller\Payment;

use LendingWorks\RetailFinance\Controller\BaseAPIHandler;
use LendingWorks\RetailFinance\Helper\Data;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Item;

class CreateOrder extends BaseAPIHandler
{
  /**
   * Execute action based on request and return result
   *
   * @return ResultInterface|ResponseInterface
   * @throws \Magento\Framework\Exception\NotFoundException
   */
    public function execute()
    {
        parent::execute();
        $quote = $this->checkoutSession->getQuote();

        if (!$quote) {
            return $this->result(404, "Order not found");
        }
        // @codingStandardsIgnoreStart JSON_PRESERVE_ZERO_FRACTION is handled in BaseAPIHandler
        $postData = json_encode($this->buildProductsData($quote), JSON_PRESERVE_ZERO_FRACTION | JSON_PRETTY_PRINT);
        // @codingStandardsIgnoreEnd
        if (json_last_error() !== JSON_ERROR_NONE) {
            return $this->result(400, 'Unable to encode createOrder message body'. json_last_error_msg());
        }

        $this->logger->debug($postData);

        $hash = hash('sha256', $postData . $quote->getCustomerEmail());

        $sessionData = $this->checkoutSession->getData(Data::ORDER_SESSION_KEY);

        if ($sessionData && !empty($sessionData[$hash])) {
            $additionalData = [
            'token' => $sessionData[$hash],
            'script_url' => $this->getRFPaymentConfig(Data::OVERRIDE_SCRIPT_SOURCE_KEY),
            ];
            return $this->result(200, 'Order token successfully loaded', $additionalData);
        }

        $response = $this->queryAPI(Data::API_CREATE_ORDER_ENDPOINT, $postData);
        if ($response instanceof ResultInterface) {
            return $response;
        }

        if ($response->getStatusCode() !== 200) {
            $message = sprintf(
                'Could not create order, non-200 HTTP code returned: %d - %s',
                $response->getStatusCode(),
                $response->getBody()
            );
            return $this->result($response->getStatusCode(), $message);
        }

        $result = json_decode($response->getBody(), true);
        if ($result === null) {
            return $this->result(400, 'Unable to decode API response');
        }

        if (!array_key_exists('token', $result)) {
            return $this->result(400, 'Invalid API response, received: ' . $result);
        }

        $this->checkoutSession->setData(Data::ORDER_SESSION_KEY, [$hash => $result['token']]);
        $additionalData = [
        'token' => $result['token'],
        'script_url' => $this->getRFPaymentConfig(Data::OVERRIDE_SCRIPT_SOURCE_KEY),
        ];
        return $this->result(200, 'Order successfully created', $additionalData);
    }

  /**
   * @param Quote $quote
   *
   * @return array
   */
    private function buildProductsData($quote)
    {
        $products = [];
        $totalDiscount = 0.0000;
        $totalTax = 0.0000;
        /** @var Item $item */
        foreach ($quote->getItems() as $item) {
            $products[] = [
            'cost' => $item->getPrice(),
            'quantity' => $item->getQty(),
            'description' => $item->getDescription() ?: $item->getName(),
            ];
            if ($item->getDiscountAmount() > 0) {
                $totalDiscount -= $item->getDiscountAmount();
            }
        }

        // Add shipping data
        $products[] = [
        'cost' => $quote->getShippingAddress()->getShippingAmount(),
        'quantity' => 1.0,
        'description' => 'Shipping: ' . $quote->getShippingAddress()->getShippingDescription()
        ];

        // Add any discount
        if ($totalDiscount < 0) {
            $products[] = [
            'cost' => number_format($totalDiscount, 4, '.', ''),
            'quantity' => 1.0,
            'description' => 'Discount'
            ];
        }

        $tax = isset($quote->getTotals()['tax']) ? $quote->getTotals()['tax'] : null;
        if ($tax !== null) {
            $totalTax = $tax->getDataByKey('value');
        }

        // Add any tax
        if ($totalTax > 0) {
            $products[] = [
            'cost' => number_format($totalTax, 4, '.', ''),
            'quantity' => 1.0,
            'description' => 'Total tax applied'
            ];
        }

        return [
        'amount' => $quote->getGrandTotal(),
        'products' => $products,
        ];
    }
}
