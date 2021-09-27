<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_PageSpeedOptimizer
 */


namespace Amasty\PageSpeedOptimizer\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;

/**
 * Class UpgradeSchema
 *
 * @package Amasty\PageSpeedOptimizer
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * @var Operation\CreateQueueTable
     */
    private $createQueueTable;

    /**
     * @var Operation\CreateBundleTable
     */
    private $createBundleTable;

    public function __construct(
        Operation\CreateQueueTable $createQueueTable,
        Operation\CreateBundleTable $createBundleTable
    ) {
        $this->createQueueTable = $createQueueTable;
        $this->createBundleTable = $createBundleTable;
    }

    /**
     * Upgrades DB schema for a module
     *
     * @param SchemaSetupInterface   $setup
     * @param ModuleContextInterface $context
     *
     * @return void
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (!$context->getVersion() || version_compare($context->getVersion(), '1.0.7', '<')) {
            $this->createQueueTable->execute($setup);
        }

        if (!$context->getVersion() || version_compare($context->getVersion(), '1.2.0', '<')) {
            $this->createBundleTable->execute($setup);
        }

        $setup->endSetup();
    }
}
