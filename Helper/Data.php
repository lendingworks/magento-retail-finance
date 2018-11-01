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
    const ORDER_ID_ATTRIBUTE_KEY = 'lendingworks_order_id';
    const ORDER_STATUS_ATTRIBUTE_KEY = 'lendingworks_order_status';
    const ORDER_FULFILMENT_STATUS_ATTRIBUTE_KEY = 'lendingworks_order_fulfilment_status';

    const OVERRIDES_KEY = 'lendingworks_retailfinance_overrides';
    const OVERRIDE_BASE_URL_KEY = 'base_url';
    const OVERRIDE_SCRIPT_SOURCE_KEY = 'script_source';
    const OVERRIDE_API_KEY_KEY = 'api_key';
    const OVERRIDE_MOCK_API_RESPONSE_KEY = 'mock_successful_api_response';

    const ORDER_STATUS_APPROVED = 'Approved';
    const ORDER_STATUS_ACCEPTED = 'Accepted';
    const ORDER_STATUS_CANCELLED = 'Cancelled';
    const ORDER_STATUS_REFERRED = 'Referred';
    const ORDER_STATUS_EXPIRED = 'Expired';
    const ORDER_STATUS_DECLINED = 'Declined';

    const ORDER_FULFILMENT_STATUS_UNFULFILLED = 'Unfulfilled';
    const ORDER_FULFILMENT_STATUS_PENDING = 'Pending';
    const ORDER_FULFILMENT_STATUS_ERROR = 'Error';
    const ORDER_FULFILMENT_STATUS_COMPLETE = 'Complete';

    const API_CREATE_ORDER_ENDPOINT = '/orders';
    const API_FULFILL_ORDER_ENDPOINT = '/orders/loan-request/fulfill';

    /**
     * @return array
     */
    public static function getOverrides()
    {
        $envPath = BP . '/app/etc/env.php';
        if (file_exists($envPath) && is_readable($envPath)) {
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
                return 'https://integration.lendingworks.co.uk/api/v2';
            case self::PRODUCTION:
                return 'https://www.lendingworks.co.uk/api/v2';
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
            case self::PRODUCTION:
                return 'https://secure.lendingworks.co.uk/checkout.js';
            default:
                return null;
        }
    }

    /**
     * @param string $status
     *
     * @return bool
     */
    public static function isValidStatus($status)
    {
        return self::in_array_i($status, [
        self::ORDER_STATUS_APPROVED,
        self::ORDER_STATUS_ACCEPTED,
        self::ORDER_STATUS_CANCELLED,
        self::ORDER_STATUS_DECLINED,
        self::ORDER_STATUS_EXPIRED,
        self::ORDER_STATUS_REFERRED
        ]);
    }

    /**
     * @param $status
     *
     * @return bool
     */
    public static function isFulfillableStatus($status)
    {
        return self::in_array_i($status, [
        self::ORDER_FULFILMENT_STATUS_UNFULFILLED,
        self::ORDER_FULFILMENT_STATUS_ERROR
        ]);
    }

    /**
     * @param string $needle
     * @param array $haystack
     *
     * @return bool
     */
    protected static function in_array_i($needle, $haystack)
    {
          return in_array(strtolower($needle), array_map('strtolower', $haystack));
    }
}
