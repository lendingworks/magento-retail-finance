<?php

namespace LendingWorks\RetailFinance\Controller\Order;

use LendingWorks\RetailFinance\Controller\BaseAPIHandler;
use LendingWorks\RetailFinance\Helper\Data;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\NotFoundException;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\OrderRepository;
use Psr\Log\LoggerInterface;

class FulfillOrder extends BaseAPIHandler
{
  /**
   * @var OrderRepository
   */
    private $orderRepository;

  /**
   * @var SearchCriteriaBuilder
   */
    private $searchCriteriaBuilder;

    public function __construct(
        Context $context,
        ScopeConfigInterface $scopeConfig,
        CheckoutSession $checkoutSession,
        LoggerInterface $logger,
        OrderRepository $orderRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        parent::__construct($context, $scopeConfig, $checkoutSession, $logger);
        $this->orderRepository = $orderRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

  /**
   * @return ResponseInterface|ResultInterface
   * @throws NotFoundException
   */
    public function execute()
    {
        $lwID = $this->getRequest()->getParam('lw_id');

        if (!$lwID) {
            $this->messageManager->addErrorMessage('Missing lw_id param');
            return $this->redirectBack();
        }

        $order = $this->getOrderByLWID($lwID);

        if (!$order) {
            return $this->redirectBack();
        }

        $postData = json_encode(['reference' => $lwID], JSON_PRESERVE_ZERO_FRACTION | JSON_PRETTY_PRINT);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return $this->result(400, 'Unable to encode fulfillOrder message body: ' . json_last_error_msg());
        }

        $response = $this->queryAPI(Data::API_FULFILL_ORDER_ENDPOINT, $postData);

        if ($response instanceof ResultInterface) {
            $this->messageManager->addErrorMessage('There was an unexpected error - please try again');
            return $this->redirectBack();
        }

        $fulfilmentStatus = Data::ORDER_FULFILMENT_STATUS_ERROR;
        switch ($response->getStatusCode()) {
            case 403:
                $message = 'Invalid credentials';
                $this->messageManager->addErrorMessage($message);
                break;
            case 400:
                $message = 'Order is in a state that cannot be fulfilled';
                $this->messageManager->addErrorMessage($message);
                break;
            case 204:
            case 200:
                $message = 'Order ' . $lwID . ' successfully fulfilled';
                $this->messageManager->addSuccessMessage($message);
                $fulfilmentStatus = Data::ORDER_FULFILMENT_STATUS_COMPLETE;
                break;
            default:
                $message = 'There was an unexpected error - please try again';
                $this->messageManager->addErrorMessage($message);
                break;
        }

        $this->logger->debug($response->getStatusCode() . ': ' . $message);
        $order->setData(Data::ORDER_FULFILMENT_STATUS_ATTRIBUTE_KEY, $fulfilmentStatus);
        $order->save();
        return $this->redirectBack();
    }

    private function redirectBack()
    {
        /** @var ResultInterface $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setUrl($this->_redirect->getRefererUrl());
        return $resultRedirect;
    }

  /**
   * @param string $lwID
   *
   * @return null|OrderInterface
   */
    private function getOrderByLWID($lwID)
    {
        $searchCriteria = $this->searchCriteriaBuilder->addFilter(
            Data::ORDER_ID_ATTRIBUTE_KEY,
            $lwID,
            'eq'
        )->create();
        $orderList = $this->orderRepository->getList($searchCriteria);
        if ($orderList->getTotalCount() > 1) {
            $this->messageManager->addErrorMessage('Multiple matches for LWID - please try again');
            return null;
        }

        if ($orderList->getTotalCount() == 0) {
            $this->messageManager->addErrorMessage('No matches for LWID - please try again');
            return null;
        }

        $items = $orderList->getItems();

        return reset($items);
    }
}
