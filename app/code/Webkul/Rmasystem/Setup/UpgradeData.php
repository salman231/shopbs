<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Rmasystem
 * @author    Webkul
 * @copyright Copyright (c) 2010-2018 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Rmasystem\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;

use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;

class UpgradeData implements UpgradeDataInterface
{
    private $eavSetupFactory;

    public function __construct(
        EavSetupFactory $eavSetupFactory
    ) {
        $this->eavSetupFactory = $eavSetupFactory;
    }

    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
        $eavSetup->addAttribute(\Magento\Catalog\Model\Category::ENTITY, 'rma_allowed', [
            'type'              => 'int',
            'label'             => 'Can Generate RMA?',
            'note'              => 'products belonging to this category can generate rma',
            'input'             => 'select',
            'source'            => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
            'visible'           => true,
            'default'           => '1',
            'required'          => false,
            'global'            => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
            'group'             => 'General Information',
            "searchable"        => false,
            "filterable"        => true,
            "comparable"        => false,
            'visible_on_front'  => false,
            'unique'            => false
        ]);
    }
}
