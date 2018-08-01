<?php

namespace LendingWorks\RetailFinance\Setup;

use LendingWorks\RetailFinance\Helper\Data;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Quote\Setup\QuoteSetup;
use Magento\Sales\Model\Order;
use Magento\Sales\Setup\SalesSetup;
use Magento\Sales\Setup\SalesSetupFactory;
use Magento\Quote\Setup\QuoteSetupFactory;

class InstallData implements InstallDataInterface
{

  /**
   * @var SalesSetupFactory
   */
    private $salesSetupFactory;

  /**
   * @var QuoteSetupFactory
   */
    private $quoteSetupFactory;

  /**
   * UpgradeData constructor.
   *
   * @param SalesSetupFactory $salesSetupFactory
   * @param QuoteSetupFactory $quoteSetupFactory
   */
    public function __construct(
        SalesSetupFactory $salesSetupFactory,
        QuoteSetupFactory $quoteSetupFactory
    ) {
        $this->salesSetupFactory = $salesSetupFactory;
        $this->quoteSetupFactory = $quoteSetupFactory;
    }

  /**
   * Installs data for a module
   *
   * @param ModuleDataSetupInterface $setup
   * @param ModuleContextInterface   $context
   *
   * @return void
   */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        /** @var SalesSetup $salesInstaller */
        $salesInstaller = $this->salesSetupFactory->create(['resourceName' => 'sales_setup', 'setup' => $setup]);

        /** @var QuoteSetup $quoteInstaller */
        $quoteInstaller = $this->quoteSetupFactory->create(['resourceName' => 'quote_setup', 'setup' => $setup]);

        //Add attributes to quote and order
        $entityAttributesCodes = [
        Data::ORDER_ID_ATTRIBUTE_KEY => Table::TYPE_TEXT,
        Data::ORDER_STATUS_ATTRIBUTE_KEY => Table::TYPE_TEXT,
        Data::ORDER_FULFILMENT_STATUS_ATTRIBUTE_KEY => Table::TYPE_TEXT,
        ];

        foreach ($entityAttributesCodes as $code => $type) {
            $options = [
            'type' => $type,
            'length'=> 255,
            'visible' => true,
            'nullable' => true,
            'comment' => self::convertCodeToComment($code)
            ];

            $connection = $setup->getConnection();
            $salesInstaller->addAttribute(Order::ENTITY, $code, $options);
            $quoteInstaller->addAttribute('quote', $code, $options);
            $connection->addColumn(
                $setup->getTable('sales_order_grid'),
                $code,
                $options
            );

            $connection->addColumn(
                $setup->getTable('quote'),
                $code,
                $options
            );
        }

        $setup->endSetup();
    }

  /**
   * @param string $code
   *
   * @return string
   */
    private static function convertCodeToComment($code)
    {
        return ucfirst(str_replace('_', ' ', $code));
    }
}
