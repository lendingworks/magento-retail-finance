<?php

namespace LendingWorks\RetailFinance\Controller\Order;

use LendingWorks\RetailFinance\Controller\BaseAPIHandler;
use LendingWorks\RetailFinance\Helper\Data;
use Magento\Framework\App\ResponseInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\NotFoundException;
use Magento\Quote\Model\QuoteRepository;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\OrderRepository;
use Psr\Log\LoggerInterface;

class Callback extends BaseAPIHandler
{
    /**
     * @var QuoteRepository
     */
    private $quoteRepository;

    /**
     * @var OrderRepository
     */
    private $orderRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

  /**
   * @param Context $context
   * @param ScopeConfigInterface $scopeConfig
   * @param CheckoutSession $checkoutSession
   * @param LoggerInterface $logger
   * @param OrderRepository $orderRepository
   * @param SearchCriteriaBuilder $searchCriteriaBuilder
   * @param QuoteRepository $quoteRepository
   */
    public function __construct(
        Context $context,
        ScopeConfigInterface $scopeConfig,
        CheckoutSession $checkoutSession,
        LoggerInterface $logger,
        OrderRepository $orderRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        QuoteRepository $quoteRepository,
        Data $dataHelper
    ) {
        parent::__construct($context, $scopeConfig, $checkoutSession, $logger, $dataHelper);
        $this->quoteRepository = $quoteRepository;
        $this->orderRepository = $orderRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;

        // Fix for Magento2.3 adding isAjax to the request params
        if (interface_exists("\Magento\Framework\App\CsrfAwareActionInterface")) {
            $request = $this->getRequest();
            if ($request instanceof HttpRequest && $request->isPost()) {
                $request->setParam('isAjax', true);
            }
        }
    }

    /**
     * Execute action based on request and return result
     *
     * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface
     * @throws NotFoundException
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute()
    {
        parent::execute();

        $data = json_decode(file_get_contents('php://input'));

        $requiredKeys = ['reference', 'status'];
        foreach ($requiredKeys as $key) {
            if (empty($data->$key)) {
                return $this->result(400, 'The request body is invalid.');
            }
        }

        $remoteStatus = $data->status;
        if (!Data::isValidStatus($remoteStatus)) {
            return $this->result(400, 'Invalid order status received: ' . $remoteStatus);
        }

        $reference = $data->reference;
        $order = $this->getOrderByLWID($reference);

        if ($order === null) {
            return $this->result(400, 'Unable to find Order with reference: '.$reference);
        }

        $order->setData(Data::ORDER_STATUS_ATTRIBUTE_KEY, $remoteStatus);
        $order->save();

        $quoteId = $order->getQuoteId();

        $quote = $this->quoteRepository->get($quoteId);

        $quote->setData(Data::ORDER_STATUS_ATTRIBUTE_KEY, $remoteStatus);
        $quote->save();

        return $this->result(200, 'Updated order status using callback api!');
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
