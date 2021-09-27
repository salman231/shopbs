<?php
/**
 * @category   Webkul
 * @package    Webkul_MpAdvancedCommission
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\MpAdvancedCommission\Setup;
 
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Customer\Model\Customer;
use Magento\Eav\Model\Entity\Attribute\Set as AttributeSet;
use Magento\Eav\Model\Entity\Attribute\SetFactory as AttributeSetFactory;
 
/**
 * @codeCoverageIgnore
 */
class InstallData implements InstallDataInterface
{
    /**
     * EAV setup factory
     *
     * @var EavSetupFactory
     */
    private $_eavSetupFactory;

     /**
      * @var CustomerSetupFactory
      */
    protected $_customerSetupFactory;
    
    /**
     * @var AttributeSetFactory
     */
    private $_attributeSetFactory;
 
    /**
     * Init
     *
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(
        EavSetupFactory $eavSetupFactory,
        CustomerSetupFactory $customerSetupFactory,
        AttributeSetFactory $attributeSetFactory
    ) {
        $this->_eavSetupFactory = $eavSetupFactory;
        $this->_customerSetupFactory = $customerSetupFactory;
        $this->_attributeSetFactory = $attributeSetFactory;
    }
 
    /**
     * {@inheritdoc}
     */
    public function install(
        ModuleDataSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $attrCode = 'commission_for_product';
        $attrGroupName = __('Prices');
        $attrLabel = __('Commission For Product');
        $attrNote = __('Commission Per Product');
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->_eavSetupFactory->create(['setup' => $setup]);
        $attrCodeExist = $eavSetup->getAttributeId(Product::ENTITY, $attrCode);
        if ($attrCodeExist === false) {
            $eavSetup->addAttribute(
                Product::ENTITY,
                $attrCode,
                [
                    'group'                 => $attrGroupName,
                    'type'                  => 'varchar',
                    'input'                 => 'text',
                    'backend'               => '',
                    'frontend'              => '',
                    'label'                 => $attrLabel,
                    'note'                  => $attrNote,
                    'frontend_class'        => 'validate-number',
                    'source'                => '',
                    'global'                => Attribute::SCOPE_GLOBAL,
                    'visible'               => true,
                    'user_defined'          => true,
                    'required'              => false,
                    'visible_on_front'      => false,
                    'unique'                => false,
                    'is_configurable'       => false,
                    'used_for_promo_rules'  => true
                ]
            );
        }

        $attrCode = 'commission_for_admin';
        $attrLabel = 'Commission For Admin';
        $attrNote = 'Commission that goes to Admin';
        $attrGroupName = 'General Information';
        $eavSetup = $this->_eavSetupFactory->create(['setup' => $setup]);
        $eavSetup->addAttribute(
            'catalog_category',
            $attrCode,
            [
                'type'                  => 'varchar',
                'group'                 => $attrGroupName,
                'input'                 => 'text',
                'backend'               => '',
                'frontend'              => '',
                'label'                 => $attrLabel,
                'note'                  => $attrNote,
                'class'                 => 'validate-zero-or-greater',
                'source'                => '',
                'global'                => Attribute::SCOPE_GLOBAL,
                'visible'               => true,
                'user_defined'          => false,
                'required'              => false,
                "default"               => "",
                "searchable"            => false,
                "filterable"            => false,
                "comparable"            => false,
                'visible_on_front'      => false,
                'unique'                => false
            ]
        );

        /* Create Customer Attribute*/

        /** @var CustomerSetup $customerSetup */
        $customerSetup = $this->_customerSetupFactory->create(['setup' => $setup]);
        
        $customerEntity = $customerSetup->getEavConfig()->getEntityType('customer');
        $attributeSetId = $customerEntity->getDefaultAttributeSetId();
        
        /** @var $attributeSet AttributeSet */
        $attributeSet = $this->_attributeSetFactory->create();
        $attributeGroupId = $attributeSet->getDefaultGroupId($attributeSetId);
        
        $customerSetup->addAttribute(
            Customer::ENTITY,
            'category_commission',
            [
                'type'              => 'text',
                'label'             => 'Category Commission',
                'input'             => 'text',
                'frontend_class'    => 'validate-zero-or-greater',
                'system'            => false,
                'global'            => true,
                'visible'           => false,
                'required'          => false,
                'user_defined'      => true,
                'default'           => '0'
            ]
        );

        $attribute = $customerSetup->getEavConfig()
        ->getAttribute(
            Customer::ENTITY,
            'category_commission'
        )
        ->addData(
            [
                'attribute_set_id' => $attributeSetId,
                'attribute_group_id' => $attributeGroupId,
                'used_in_forms' => [
                    'adminhtml_customer'
                ]
            ]
        );
        $attribute->save();
    }
}
