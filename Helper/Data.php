<?php

namespace LendingWorks\RetailFinance\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

class Data extends AbstractHelper
{
  const PRODUCTION = 'prod';
  const PRODUCTION_LABEL = 'Production';
  const TESTING = 'int';
  const TESTING_LABEL = 'Integration/Testing';

  const PAYMENT_CODE = 'lendingworks_retailfinance';
  const ORDER_SESSION_KEY = 'lw-rf-order-id';

  const OVERRIDES_KEY = 'lendingworks_retailfinance_overrides';
  const OVERRIDE_BASE_URL_KEY = 'base_url';
  const OVERRIDE_SCRIPT_SOURCE_KEY = 'script_source';
  const OVERRIDE_API_KEY_KEY = 'api_key';
  const OVERRIDE_MOCK_API_RESPONSE_KEY = 'mock_successful_API_response';

  /**
   * @return array
   */
  public static function getOverrides()
  {
    $envPath = BP . '/app/etc/env.php';
    if (file_exists($envPath)  && is_readable($envPath)) {
      $envData = include $envPath;
      if (isset($envData[self::OVERRIDES_KEY])) {
        return $envData[self::OVERRIDES_KEY];
      }
    }

    return [];
  }

  /**
   * @param string $environment
   *
   * @return string|null
   */
  public static function getBaseURLForEnvironment($environment)
  {
    if (isset(self::getOverrides()[self::OVERRIDE_BASE_URL_KEY])) {
      return self::getOverrides()[self::OVERRIDE_BASE_URL_KEY];
    }

    switch ($environment) {
      case self::TESTING:
        return 'http://integration.lendingworks.co.uk/api/v2';
        break;
      case self::PRODUCTION:
        return 'https://www.lendingworks.co.uk/api/v2';
        break;
      default:
        return null;
    }
  }

  /**
   * @param string $environment
   *
   * @return string|null
   */
  public static function getCheckoutScriptSourceForEnvironment($environment)
  {
    if (isset(self::getOverrides()[self::OVERRIDE_SCRIPT_SOURCE_KEY])) {
      return self::getOverrides()[self::OVERRIDE_SCRIPT_SOURCE_KEY];
    }
    switch ($environment) {
      case self::TESTING:
        return 'https://integration.secure.lendingworks.co.uk/checkout.js';
        break;
      case self::PRODUCTION:
        return 'https://secure.lendingworks.co.uk/checkout.js';
        break;
      default:
        return null;
    }
  }
}