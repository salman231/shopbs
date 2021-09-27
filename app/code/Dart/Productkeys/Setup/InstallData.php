<?php
/**
 * Productkeys Productkeys Product Attributes.
 * @category  Dart
 * @package   Dart_Productkeys
 *
 */
namespace Dart\Productkeys\Setup;

use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Email\Model\TemplateFactory;
use Magento\Email\Model\ResourceModel\Template\CollectionFactory;
use Magento\Framework\Module\Dir\Reader;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\App\TemplateTypesInterface;

class InstallData implements InstallDataInterface
{
    private $eavSetupFactory;

    private $templateFactory;

    public function __construct(
        EavSetupFactory $eavSetupFactory,
        TemplateFactory $templateFactory,
        CollectionFactory $templateCollection,
        Reader $directoryList,
        File $fileSystem
    ) {
        $this->directoryList = $directoryList;
        $this->eavSetupFactory = $eavSetupFactory;
        $this->templateFactory = $templateFactory;
        $this->templateCollection = $templateCollection;
        $this->fileSystem = $fileSystem;
    }

    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'productkey_overwritegnrlconfig',
            [
                'type' => 'int',
                'backend' => '',
                'frontend' => '',
                'label' => 'Overwrite General Configuration',
                'input' => 'select',
                'class' => '',
                'source' => 'Dart\Productkeys\Model\Config\Source\Options',
                'backend' => 'Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend',
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                'visible' => true,
                'required' => false,
                'user_defined' => true,
                'default' => 0,
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => true,
                'unique' => false,
                'apply_to' => '',
                'sort_order' => 1,
                'note' => 'Overwrite general configuration and use different values for this product.'
            ]
        );

        /* Label of the group */
        $groupName = 'Product Keys';
        /* get entity type id to assign attribute only to catalog_product. */
        $entityTypeId = $eavSetup->getEntityTypeId('catalog_product');
        /* Fetch all attribute set so that our attribute group shows under all attribute set. */
        $attributeSetIds = $eavSetup->getAllAttributeSetIds($entityTypeId);

        foreach ($attributeSetIds as $attributeSetId) {
            $eavSetup->addAttributeGroup($entityTypeId, $attributeSetId, $groupName, 10);
            $attributeGroupId = $eavSetup->getAttributeGroupId($entityTypeId, $attributeSetId, $groupName);
            // Add existing attribute to group
            $attributeId = $eavSetup->getAttributeId($entityTypeId, 'productkey_overwritegnrlconfig');
            $eavSetup->addAttributeToGroup($entityTypeId, $attributeSetId, $attributeGroupId, $attributeId, 1);
        }

        $warning_temp_exists = $this->templateCollection->create()
                        ->addFieldToFilter('template_code', 'Productkey Warning');
        if (count($warning_temp_exists) == 0) {
            $template_warning = $this->templateFactory->create();
            $filePathWarn = $this->directoryList
                        ->getModuleDir('', 'Dart_Productkeys').'/view/frontend/email/productkey_warning.html';
            $text_warning = $this->fileSystem->fileGetContents($filePathWarn);
            $lowTxt = 'Warning: {{var keytype}}s {{depend available_keys}}are getting low.{{/depend}}';
            $allUsedTxt = '{{depend available_keys_none}}have all been used!{{/depend}}';
            $template_warning->setTemplateCode('Productkey Warning')
                ->setTemplateText($text_warning)
                ->setTemplateSubject($lowTxt.$allUsedTxt)
                ->setTemplateType(TemplateTypesInterface::TYPE_HTML);
            $template_warning->save();
        }

        $delivery_temp_exists = $this->templateCollection->create()
                        ->addFieldToFilter('template_code', 'Productkey Delivery');
        if (count($delivery_temp_exists) == 0) {
            $template_email = $this->templateFactory->create();
            $filePath = $this->directoryList
                        ->getModuleDir('', 'Dart_Productkeys').'/view/frontend/email/productkey_delivery.html';
            $text_email = $this->fileSystem->fileGetContents($filePath);
            $template_email->setTemplateCode('Productkey Delivery')
                ->setTemplateText($text_email)
                ->setTemplateSubject('{{var order.getStoreGroupName()}}: Your {{var keytype}}(s)')
                ->setTemplateType(TemplateTypesInterface::TYPE_HTML);
            $template_email->save();
        }
    }
}
