<?php

namespace LendingWorks\RetailFinance\Model;

use LendingWorks\RetailFinance\Helper\Data as LWData;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Model\Context;
use Magento\Framework\Module\ModuleListInterface;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Payment\Helper\Data;
use Magento\Payment\Model\InfoInterface;
use Magento\Payment\Model\Method\AbstractMethod;
use Magento\Payment\Model\Method\Logger;
use Magento\Quote\Api\Data\CartInterface;

class Payment extends AbstractMethod
{
  protected $_code = LWData::PAYMENT_CODE;

  protected $_isGateway                   = true;
  protected $_canCapture                  = true;
  protected $_canCapturePartial           = true;
  protected $_canRefund                   = false;
  protected $_canRefundInvoicePartial     = false;

  protected $_guzzleClient = false;

  protected $_minAmount = null;
  protected $_maxAmount = null;
  protected $_supportedCurrencyCodes = ['GBP', 'USD'];

  protected $_targetServer = LWData::TESTING;
  protected $_remoteOrderToken = null;

  public function __construct(
    Context $context,
    Registry $registry,
    ExtensionAttributesFactory $extensionFactory,
    AttributeValueFactory $customAttributeFactory,
    Data $paymentData,
    ScopeConfigInterface $scopeConfig,
    Logger $logger,
    ModuleListInterface $moduleList,
    TimezoneInterface $localeDate,
    array $data = []
  ) {
    parent::__construct(
      $context,
      $registry,
      $extensionFactory,
      $customAttributeFactory,
      $paymentData,
      $scopeConfig,
      $logger,
      null,
      null,
      $data
    );
    $this->_minAmount = $this->getConfigData('min_order_total');
    $this->_maxAmount = $this->getConfigData('max_order_total');
    $this->_targetServer = $this->getConfigData('target_server');

    $this->_guzzleClient = new \GuzzleHttp\Client();
  }

  /**
   * Payment capturing
   *
   * @param InfoInterface $payment
   * @param float $amount
   * @return $this
   * @throws \Magento\Framework\Validator\Exception
   */
  public function capture(InfoInterface $payment, $amount)
  {
    /** @var \Magento\Sales\Model\Order $order */
    $order = $payment->getOrder();
    $products = [];
    foreach ($order->getItems() as $item) {
      $products[] = [
        'cost' => $item->getBaseCost(),
        'quantity' => $item->getQtyOrdered(),
        'description' => $item->getDescription()
      ];
    }

    /** @var \Magento\Sales\Model\Order\Address $billing */
    $billing = $order->getBillingAddress();

//    try {
      $requestData = [
        'amount' => $amount * 100,
        'products' => $products
      ];

      throw new \Magento\Framework\Validator\Exception(__('Inside Payment capture method, throwing donuts :]'));

      $payment
        ->setTransactionId($charge->id)
        ->setIsTransactionClosed(0);

//    } catch (\Exception $e) {
//      $this->debugData(['request' => $requestData, 'exception' => $e->getMessage()]);
//      $this->_logger->error(__('Payment capturing error.'));
//      throw new \Magento\Framework\Validator\Exception(__('Payment capturing error.'));
//    }

    return $this;
  }

  /**
   * Determine method availability based on quote amount and config data
   *
   * @param CartInterface|null $quote
   * @return bool
   */
  public function isAvailable(CartInterface $quote = null)
  {
    if ($quote && (
        $quote->getBaseGrandTotal() < $this->_minAmount
        || ($this->_maxAmount && $quote->getBaseGrandTotal() > $this->_maxAmount))
    ) {
      return false;
    }

    if (!$this->getConfigData('api_key')) {
      return false;
    }

    return parent::isAvailable($quote);
  }

  /**
   * Availability for currency
   *
   * @param string $currencyCode
   * @return bool
   */
  public function canUseForCurrency($currencyCode)
  {
    if (!in_array($currencyCode, $this->_supportedCurrencyCodes)) {
      return false;
    }
    return true;
  }
}