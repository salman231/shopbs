<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_AutoRelated
 * @copyright   Copyright (c) Mageplaza (http://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\AutoRelated\Setup;

use Magento\Customer\Model\Customer;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UninstallInterface;

/**
 * Class Uninstall
 * @package Mageplaza\AutoRelated\Setup
 */
class Uninstall implements UninstallInterface
{
    /**
     * @var EavSetupFactory
     */
    protected $eavSetupFactory;

    /**
     * Uninstall constructor.
     *
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(EavSetupFactory $eavSetupFactory)
    {
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * Invoked when remove-data flag is set during module uninstall.
     *
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     *
     * @return void
     */
    public function uninstall(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        if ($installer->tableExists('mageplaza_autorelated_block_rule')) {
            $installer->getConnection()->dropTable($installer->getTable('mageplaza_autorelated_block_rule'));
        }
        if ($installer->tableExists('mageplaza_autorelated_block_rule_store')) {
            $installer->getConnection()->dropTable($installer->getTable('mageplaza_autorelated_block_rule_store'));
        }
        if ($installer->tableExists('mageplaza_autorelated_block_rule_customer_group')) {
            $installer->getConnection()->dropTable($installer->getTable('mageplaza_autorelated_block_rule_customer_group'));
        }
        if ($installer->tableExists('mageplaza_autorelated_actions_index')) {
            $installer->getConnection()->dropTable($installer->getTable('mageplaza_autorelated_actions_index'));
        }

        $eavSetup = $this->eavSetupFactory->create();
        $eavSetup->removeAttribute(Customer::ENTITY, 'mp_disable_auto_related');

        $installer->endSetup();
    }
}
