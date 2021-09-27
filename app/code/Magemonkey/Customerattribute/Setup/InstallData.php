<?php
namespace Magemonkey\Customerattribute\Setup;
 
use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Model\Config as EavConfig;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Catalog\Model\Product;
 
class InstallData implements InstallDataInterface
{
    private $eavSetupFactory;
    /**
     * @var EavConfig
     */
    private $eavConfig;
 
    public function __construct(EavSetupFactory $eavSetupFactory, EavConfig $eavConfig)
    {
        $this->eavSetupFactory = $eavSetupFactory;
        $this->eavConfig = $eavConfig;
    }
 
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $eavsetup = $this->eavSetupFactory->create(['setup' => $setup]);
        $attributecode = 'phone';
 
        $eavsetup->addAttribute(CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER, $attributecode, [
            'label' => 'Phone Number',
            'required' => 0,
            'user_defined' => 1,
            'note' => 'Separate Multiple Intrests with comms',
            'system' => 0,
            'position' => 100,
        ]);
        
        $eavsetup->addAttributeToSet(
            CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER,
            CustomerMetadataInterface::ATTRIBUTE_SET_ID_CUSTOMER,
            null,
            $attributecode
        );
 
        $attribute = $this->eavConfig->getAttribute(CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER, $attributecode);
        $attribute->setData('used_in_forms', [
            'adminhtml_customer',
            'customer_account_create',
            'customer_account_edit'
        ]);
 
        $attribute->setData('validate_rules', [
            'input_validation' => 1,
            'min_text_length' => 3,
            'max_text_length' => 30,
 
        ]);


        $attribute->getResource()->save($attribute);

        ##--------------- 

        $eavsetup1 = $this->eavSetupFactory->create(['setup' => $setup]);
        $attributecode1 = 'businessname';
 
        $eavsetup1->addAttribute(CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER, $attributecode1, [
            'label' => 'Business Name',
            'required' => 0,
            'user_defined' => 1,
            'note' => 'Separate Multiple Intrests with comms',
            'system' => 0,
            'position' => 100,
        ]);
        
        $eavsetup1->addAttributeToSet(
            CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER,
            CustomerMetadataInterface::ATTRIBUTE_SET_ID_CUSTOMER,
            null,
            $attributecode1
        );
 
        $attribute1 = $this->eavConfig->getAttribute(CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER, $attributecode1);
        $attribute1->setData('used_in_forms', [
            'adminhtml_customer',
            'customer_account_create',
            'customer_account_edit'
        ]);
 
        $attribute1->setData('validate_rules', [
            'input_validation' => 1,
            'min_text_length' => 3,
            'max_text_length' => 30,
 
        ]);

        
        $attribute1->getResource()->save($attribute1);

    }
}