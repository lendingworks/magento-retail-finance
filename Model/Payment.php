<?php

namespace LendingWorks\RetailFinance\Model;

use LendingWorks\RetailFinance\Helper\Data as LWData;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Payment\Helper\Data;
use Magento\Payment\Model\Method\AbstractMethod;
use Magento\Payment\Model\Method\Logger;
use Magento\Quote\Api\Data\CartInterface;

class Payment extends AbstractMethod
{
    protected $_code = LWData::PAYMENT_CODE;

    protected $_isGateway = true;
    protected $_canCapture = true;

    protected $_minAmount;
    protected $_maxAmount;
    protected $_supportedCurrencyCodes = ['GBP'];

    protected $_allow_manual_fulfilment;

    protected $_targetServer = LWData::TESTING;

    public function __construct(
        Context $context,
        Registry $registry,
        ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $customAttributeFactory,
        Data $paymentData,
        ScopeConfigInterface $scopeConfig,
        Logger $logger,
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
        $this->_allow_manual_fulfilment = $this->getConfigData('allow_manual_fulfilment');
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
