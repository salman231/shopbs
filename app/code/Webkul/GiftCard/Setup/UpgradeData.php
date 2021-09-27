<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_GiftCard
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\GiftCard\Setup;

use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Sales\Setup\SalesSetupFactory;
use Magento\Quote\Setup\QuoteSetupFactory;

class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var Magento\Sales\Setup\SalesSetupFactory
     */
    protected $_salesSetupFactory;
 
    /**
     * @var Magento\Quote\Setup\QuoteSetupFactory
     */
    protected $_quoteSetupFactory;

    public function __construct(
        SalesSetupFactory $salesSetupFactory,
        QuoteSetupFactory $quoteSetupFactory
    ) {
        $this->_salesSetupFactory = $salesSetupFactory;
        $this->_quoteSetupFactory = $quoteSetupFactory;
    }

    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        /** @var \Magento\Quote\Setup\QuoteSetup $quoteInstaller */
        $quoteInstaller = $this->_quoteSetupFactory
                        ->create(
                            [
                                'resourceName' => 'quote_setup',
                                'setup' => $setup
                            ]
                        );
 
        $this->addQuoteAttributes($quoteInstaller);
    }

    /**
     * add attribute in quote address
     * @param object $installer
     */
    public function addQuoteAttributes($installer)
    {
        $installer->addAttribute('quote', 'basefee', ['type' => 'float']);
    }
}
