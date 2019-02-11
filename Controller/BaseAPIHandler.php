<?php

namespace LendingWorks\RetailFinance\Controller;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Magento\Checkout\Model\Session as CheckoutSession;
use LendingWorks\RetailFinance\Helper\Data;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Psr\Log\LoggerInterface;

abstract class BaseAPIHandler extends Action
{
  /**
   * @var Client
   */
    protected $guzzleClient;

  /**
   * @var ScopeConfigInterface
   */
    protected $scopeConfig;

  /**
   * @var LoggerInterface
   */
    protected $logger;

  /**
   * @var CheckoutSession
   */
    protected $checkoutSession;

    public function __construct(
        Context $context,
        ScopeConfigInterface $scopeConfig,
        CheckoutSession $checkoutSession,
        LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->scopeConfig = $scopeConfig;
        $this->checkoutSession = $checkoutSession;
        $this->logger = $logger;

        // Handles pre-5.6.6 versions of PHP 5.6
        if (!defined('JSON_PRESERVE_ZERO_FRACTION')) {
            define('JSON_PRESERVE_ZERO_FRACTION', 1024);
        }
    }

  /**
   * Execute action based on request and return result
   *
   * @return ResultInterface|ResponseInterface
   * @throws \Magento\Framework\Exception\NotFoundException
   */
    public function execute()
    {
        $method = $this->getRequest()->getMethod();
        if ($method !== 'POST') {
            $this->logger->debug('Received ' . $method . ' instead of POST to ' . $this->getRequest()->getActionName());
            return $this->result(405, 'Method not allowed - use POST');
        }
    }

  /**
   * Helper for config values that checks for overrides
   *
   * @param string $key
   * @return mixed
   */
    protected function getRFPaymentConfig($key)
    {
        if (!empty(Data::getOverrides()) && isset(Data::getOverrides()[$key])) {
            $this->logger->debug('Using override for ' . $key);
            return Data::getOverrides()[$key];
        }
        return $this->scopeConfig->getValue('payment/' . Data::PAYMENT_CODE . '/' . $key);
    }

  /**
   * @return Client
   */
    protected function getClient()
    {
        if (!$this->guzzleClient) {
            $this->guzzleClient = new Client();
        }
        return $this->guzzleClient;
    }

  /**
   * @param int $statusCode
   * @param string $message
   * @param array $additionalData
   *
   * @return ResultInterface
   */
    protected function result($statusCode, $message, $additionalData = [])
    {
        if ($statusCode === 200) {
            $this->messageManager->addSuccessMessage($message);
        } else {
            $this->messageManager->addErrorMessage($message);
        }
        $return = $this->resultFactory->create(ResultFactory::TYPE_JSON)->setHttpResponseCode($statusCode);
        $data = array_merge($additionalData, ['message' => __($message)]);
        $return->setData($data);

        return $return;
    }

  /**
   * @param string $endpoint
   * @param string $postData JSON-encoded array
   *
   * @return Response|ResultInterface
   */
    protected function queryAPI($endpoint, $postData)
    {
        if (isset(Data::getOverrides()[Data::OVERRIDE_MOCK_API_RESPONSE_KEY])) {
            return new Response(200, [], '{"token": "mock_turtle_soup"}');
        }
        $apiURL = Data::getBaseURLForEnvironment($this->getRFPaymentConfig('target_server')) . $endpoint;
        $apiKey = $this->getRFPaymentConfig(Data::OVERRIDE_API_KEY_KEY);
        $headers = [
        'Content-type' => 'application/json',
        'Authorization' => 'RetailApiKey ' . $apiKey,
        ];
        $request = new Request('POST', $apiURL, $headers, $postData);
        try {
            return $this->getClient()->send($request);
        } catch (GuzzleException $e) {
            $this->logger->debug('Error querying Lending Works API: ' . $e->getMessage());
            return $this->result(400, 'Unable to query Lending Works API');
        }
    }
}
