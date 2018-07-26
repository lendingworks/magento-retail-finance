<?php

namespace LendingWorks\RetailFinance\Model\Source;

use LendingWorks\RetailFinance\Helper\Data;
use Magento\Framework\Option\ArrayInterface;

class TargetServerType implements ArrayInterface
{
  /**
   * Options getter
   *
   * @return array
   */
  public function toOptionArray()
  {
    return [
      ['value' => Data::TESTING, 'label' => __(Data::TESTING_LABEL)],
      ['value' => Data::PRODUCTION, 'label' => __(Data::PRODUCTION_LABEL)],
    ];
  }

  /**
   * Get options in "key-value" format
   *
   * @return array
   */
  public function toArray()
  {
    return [
      Data::TESTING => __(Data::TESTING_LABEL),
      Data::PRODUCTION => __(Data::PRODUCTION_LABEL),
    ];
  }

}