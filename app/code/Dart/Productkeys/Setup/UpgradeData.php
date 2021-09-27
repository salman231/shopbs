<?php
/**
 * Productkeys Productkeys Product Attributes.
 * @category  Dart
 * @package   Dart_Productkeys
 *
 */
namespace Dart\Productkeys\Setup;

use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Email\Model\TemplateFactory;
use Magento\Email\Model\ResourceModel\Template\CollectionFactory;
use Magento\Framework\Module\Dir\Reader;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\App\TemplateTypesInterface;

class UpgradeData implements UpgradeDataInterface
{
    public function __construct(
        EavSetupFactory $eavSetupFactory,
        TemplateFactory $templateFactory,
        CollectionFactory $templateCollection,
        Reader $directoryList,
        File $fileSystem
    ) {
        $this->eavSetupFactory = $eavSetupFactory;
        $this->directoryList = $directoryList;
        $this->templateFactory = $templateFactory;
        $this->templateCollection = $templateCollection;
        $this->fileSystem = $fileSystem;
    }

    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
        if (version_compare($context->getVersion(), '1.2', '<')) {
            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'productkey_issue_invoice',
                [
                    'type' => 'int',
                    'backend' => '',
                    'frontend' => '',
                    'label' => 'Issue When Invoiced',
                    'input' => 'select',
                    'class' => 'productkey_issue_invoice',
                    'source' => 'Dart\Productkeys\Model\Config\Source\Options',
                    'backend' => 'Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend',
                    'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                    'visible' => true,
                    'required' => false,
                    'user_defined' => true,
                    'default' => '',
                    'searchable' => false,
                    'filterable' => false,
                    'comparable' => false,
                    'visible_on_front' => false,
                    'used_in_product_listing' => false,
                    'unique' => false,
                    'apply_to' => '',
                    'sort_order' => 2,
                    'group' => 'Product Keys',
                    'note' => 'Enable automatic issuing of productkeys to order when invoiced and paid.'
                ]
            );

            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'productkey_updatestock',
                [
                    'type' => 'int',
                    'backend' => '',
                    'frontend' => '',
                    'label' => 'Update Product Quantity and Stock',
                    'input' => 'select',
                    'class' => 'productkey_updatestock',
                    'source' => 'Dart\Productkeys\Model\Config\Source\Options',
                    'backend' => 'Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend',
                    'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                    'visible' => true,
                    'required' => false,
                    'user_defined' => true,
                    'default' => '',
                    'searchable' => false,
                    'filterable' => false,
                    'comparable' => false,
                    'visible_on_front' => false,
                    'used_in_product_listing' => false,
                    'unique' => false,
                    'apply_to' => '',
                    'sort_order' => 3,
                    'group' => 'Product Keys',
                    'note' => 'Update product quantity and stock status based on number of productkeys available.'
                ]
            );

            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'productkey_type',
                [
                    'type' => 'varchar',
                    'backend' => '',
                    'frontend' => '',
                    'label' => 'Product Key Type',
                    'input' => 'text',
                    'class' => 'productkey_type',
                    'source' => '',
                    'backend' => 'Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend',
                    'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                    'visible' => true,
                    'required' => false,
                    'user_defined' => true,
                    'default' => '',
                    'searchable' => false,
                    'filterable' => false,
                    'comparable' => false,
                    'visible_on_front' => false,
                    'used_in_product_listing' => false,
                    'unique' => false,
                    'apply_to' => '',
                    'sort_order' => 4,
                    'group' => 'Product Keys',
                    'note' => 'Title to be sent along with keys (e.g. License Key, Activation Key).
                        Defaults to "Productkey". if left empty'
                ]
            );

            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'productkey_pool',
                [
                    'type' => 'varchar',
                    'backend' => '',
                    'frontend' => '',
                    'label' => 'Key Pool Name',
                    'input' => 'text',
                    'class' => 'productkey_pool',
                    'source' => '',
                    'backend' => 'Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend',
                    'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                    'visible' => true,
                    'required' => false,
                    'user_defined' => true,
                    'default' => '',
                    'searchable' => false,
                    'filterable' => false,
                    'comparable' => false,
                    'visible_on_front' => false,
                    'used_in_product_listing' => false,
                    'unique' => false,
                    'apply_to' => '',
                    'sort_order' => 5,
                    'group' => 'Product Keys',
                    'note' => 'Leave empty to use product SKU.'
                ]
            );

            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'productkey_not_available',
                [
                    'type' => 'varchar',
                    'backend' => '',
                    'frontend' => '',
                    'label' => 'Not Available Message',
                    'input' => 'text',
                    'class' => 'productkey_not_available',
                    'source' => '',
                    'backend' => 'Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend',
                    'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                    'visible' => true,
                    'required' => false,
                    'user_defined' => true,
                    'default' => '',
                    'searchable' => false,
                    'filterable' => false,
                    'comparable' => false,
                    'visible_on_front' => false,
                    'used_in_product_listing' => false,
                    'unique' => false,
                    'apply_to' => '',
                    'sort_order' => 6,
                    'group' => 'Product Keys',
                    'note' => 'Message displayed to customer when no productkeys are available to issue.
                        Defaults to "Oops! No Productkey Available right now. Please call or email.", if left empty.'
                ]
            );

            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'productkey_send_email',
                [
                    'type' => 'int',
                    'backend' => '',
                    'frontend' => '',
                    'label' => 'Send Customer Email',
                    'input' => 'select',
                    'class' => 'productkey_send_email',
                    'source' => 'Dart\Productkeys\Model\Config\Source\Options',
                    'backend' => 'Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend',
                    'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                    'visible' => true,
                    'required' => false,
                    'user_defined' => true,
                    'default' => '',
                    'searchable' => false,
                    'filterable' => false,
                    'comparable' => false,
                    'visible_on_front' => false,
                    'used_in_product_listing' => false,
                    'unique' => false,
                    'apply_to' => '',
                    'sort_order' => 7,
                    'group' => 'Product Keys',
                    'note' => 'Email productkeys to customer when they are automatically issued.'
                ]
            );

            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'productkey_email_template',
                [
                    'type' => 'int',
                    'backend' => '',
                    'frontend' => '',
                    'label' => 'Delivery Email Template',
                    'input' => 'select',
                    'class' => 'productkey_email_template',
                    'source' => 'Dart\Productkeys\Model\Config\Source\Templateoptions',
                    'backend' => 'Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend',
                    'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                    'visible' => true,
                    'required' => false,
                    'user_defined' => true,
                    'default' => 'productkeys_delivery',
                    'searchable' => false,
                    'filterable' => false,
                    'comparable' => false,
                    'visible_on_front' => false,
                    'used_in_product_listing' => false,
                    'unique' => false,
                    'apply_to' => '',
                    'sort_order' => 8,
                    'group' => 'Product Keys',
                    'note' => 'Email template to use for delivering productkeys to customer.'
                ]
            );

            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'productkey_low_warning',
                [
                    'type' => 'int',
                    'backend' => '',
                    'frontend' => '',
                    'label' => 'Send Low Warning Email',
                    'input' => 'select',
                    'class' => 'productkey_low_warning',
                    'source' => 'Dart\Productkeys\Model\Config\Source\Options',
                    'backend' => 'Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend',
                    'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                    'visible' => true,
                    'required' => false,
                    'user_defined' => true,
                    'default' => '',
                    'searchable' => false,
                    'filterable' => false,
                    'comparable' => false,
                    'visible_on_front' => false,
                    'used_in_product_listing' => false,
                    'unique' => false,
                    'apply_to' => '',
                    'sort_order' => 9,
                    'group' => 'Product Keys',
                    'note' => 'Send warning message by email when remaining productkeys are getting low.'
                ]
            );

            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'productkey_warning_template',
                [
                    'type' => 'int',
                    'backend' => '',
                    'frontend' => '',
                    'label' => 'Low Warning Template',
                    'input' => 'select',
                    'class' => 'productkey_warning_template',
                    'source' => 'Dart\Productkeys\Model\Config\Source\Templateoptions',
                    'backend' => 'Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend',
                    'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                    'visible' => true,
                    'required' => false,
                    'user_defined' => true,
                    'default' => 'productkeys_warning',
                    'searchable' => false,
                    'filterable' => false,
                    'comparable' => false,
                    'visible_on_front' => false,
                    'used_in_product_listing' => false,
                    'unique' => false,
                    'apply_to' => '',
                    'sort_order' => 10,
                    'group' => 'Product Keys',
                    'note' => 'Email template to use for the warning message.'
                ]
            );

            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'productkey_warning_level',
                [
                    'type' => 'int',
                    'backend' => '',
                    'frontend' => '',
                    'label' => 'Low Warning Level',
                    'input' => 'text',
                    'class' => 'productkey_warning_level',
                    'source' => '',
                    'backend' => 'Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend',
                    'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                    'visible' => true,
                    'required' => false,
                    'user_defined' => true,
                    'default' => '',
                    'searchable' => false,
                    'filterable' => false,
                    'comparable' => false,
                    'visible_on_front' => false,
                    'used_in_product_listing' => false,
                    'unique' => false,
                    'apply_to' => '',
                    'sort_order' => 11,
                    'group' => 'Product Keys',
                    'note' => 'Notify when remaining productkeys reaches this number.'
                ]
            );

            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'productkey_warning_email',
                [
                    'type' => 'varchar',
                    'backend' => '',
                    'frontend' => '',
                    'label' => 'Email Low Warning To',
                    'input' => 'text',
                    'class' => 'productkey_warning_email',
                    'source' => '',
                    'backend' => 'Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend',
                    'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                    'visible' => true,
                    'required' => false,
                    'user_defined' => true,
                    'default' => '',
                    'searchable' => false,
                    'filterable' => false,
                    'comparable' => false,
                    'visible_on_front' => false,
                    'used_in_product_listing' => false,
                    'unique' => false,
                    'apply_to' => '',
                    'sort_order' => 12,
                    'group' => 'Product Keys',
                    'note' => 'Separate each provided email address with semicolons'
                ]
            );
        }
        if (version_compare($context->getVersion(), '1.2', '>')) {
            $delivery_temp = $this->templateCollection->create()
                            ->addFieldToFilter('template_code', 'Productkey Delivery');
            if (count($delivery_temp) > 0) {
                $filePath = $this->directoryList
                        ->getModuleDir('', 'Dart_Productkeys').'/view/frontend/email/productkey_delivery.html';
                $text_email = $this->fileSystem->fileGetContents($filePath);
                foreach ($delivery_temp as $template) {
                    $template->setTemplateText($text_email)
                        ->setTemplateSubject('{{var storeGroupName}}: Your {{var keytype}}(s)')
                        ->setTemplateType(TemplateTypesInterface::TYPE_HTML);
                    $template->save();
                }
            }
            $warning_temp = $this->templateCollection->create()
                            ->addFieldToFilter('template_code', 'Productkey Warning');
            if (count($warning_temp) > 0) {
                $filePathWarn = $this->directoryList
                            ->getModuleDir('', 'Dart_Productkeys').'/view/frontend/email/productkey_warning.html';
                $text_warning = $this->fileSystem->fileGetContents($filePathWarn);
                foreach ($warning_temp as $template) {
                    $template->setTemplateText($text_warning)->setTemplateType(TemplateTypesInterface::TYPE_HTML);
                    $template->save();
                }
            }
        }
    }
}
