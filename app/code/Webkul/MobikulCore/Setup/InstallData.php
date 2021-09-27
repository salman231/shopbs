<?php
/**
 * Webkul Software.
 *
 *
 *
 * @category  Webkul
 * @package   Webkul_MobikulCore
 * @author    Webkul <support@webkul.com>
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html ASL Licence
 * @link      https://store.webkul.com/license.html
 */

namespace Webkul\MobikulCore\Setup;

use Magento\Eav\Model\Config;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Eav\Model\Entity\Attribute\Set as AttributeSet;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Catalog\Model\ResourceModel\Product as ResourceProduct;
use Magento\Eav\Model\Entity\Attribute\SetFactory as AttributeSetFactory;

/**
 * Install Data Class to create attributes
 */
class InstallData implements InstallDataInterface
{
    protected $reader;
    protected $eavConfig;
    protected $fileSystem;
    protected $attributeSet;
    protected $filesystemFile;
    protected $eavSetupFactory;
    protected $resourceProduct;

    public function __construct(
        Config $eavConfig,
        AttributeSet $attributeSet,
        ResourceProduct $resourceProduct,
        EavSetupFactory $eavSetupFactory,
        \Magento\Framework\Filesystem $fileSystem,
        \Magento\Framework\Module\Dir\Reader $reader,
        \Magento\Framework\Filesystem\Io\File $filesystemFile,
        AttributeSetFactory $attributeSetFactory
    ) {
        $this->reader = $reader;
        $this->eavConfig = $eavConfig;
        $this->fileSystem = $fileSystem;
        $this->attributeSet = $attributeSet;
        $this->filesystemFile = $filesystemFile;
        $this->eavSetupFactory = $eavSetupFactory;
        $this->resourceProduct = $resourceProduct;
        $this->attributeSetFactory  = $attributeSetFactory;
    }

    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $eavSetup = $this->eavSetupFactory->create(["setup"=>$setup]);
        $eavSetup->removeAttribute(\Magento\Catalog\Model\Product::ENTITY, "as_featured");
        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            "as_featured",
            [
                "group" => "Product Details",
                "used_in_product_listing" => true,
                "filterable" => false,
                "input" => "boolean",
                "label" => "Is featured for Mobikul ?",
                "global" => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                "comparable" => false,
                "searchable" => false,
                "user_defined" => true,
                "visible_on_front" => false,
                "visible_in_advanced_search" => false,
                "is_html_allowed_on_front" => false,
                "required" => false,
                "unique" => false,
                "is_configurable" => false
            ]
        );
        $entityType = $this->resourceProduct->getEntityType();
        $attributeSetCollection = $this->attributeSet->setEntityTypeFilter($entityType);
        foreach ($attributeSetCollection as $attributeSet) {
            $eavSetup->addAttributeToSet(
                "catalog_product",
                $attributeSet->getAttributeSetName(),
                "General",
                "as_featured"
            );
        }
        $eavSetup = $this->eavSetupFactory->create(["setup" => $setup]);
        $this->moveDirToMediaDir();
        $groupName = "Mobikul Configuration";
        $entityTypeId = $eavSetup->getEntityTypeId("catalog_product");
        $attributeSetIds = $eavSetup->getAllAttributeSetIds($entityTypeId);
        foreach ($attributeSetIds as $attributeSetId) {
            $eavSetup->addAttributeGroup($entityTypeId, $attributeSetId, $groupName, 2);
            $attributeGroupId = $eavSetup->getAttributeGroupId($entityTypeId, $attributeSetId, $groupName);
            $attributeId = $eavSetup->getAttributeId($entityTypeId, "as_featured");
            $eavSetup->addAttributeToGroup($entityTypeId, $attributeSetId, $attributeGroupId, $attributeId, null);
        }
        $this->eavConfig->getAttribute("catalog_product", "as_featured")->setSortOrder(1)->save();
        $typeSource = \Webkul\MobikulCore\Model\Config\Source\ArOptions::class;

        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            "ar_type",
            [
                "group" => "Mobikul Configuration",
                "type" => "varchar",
                "backend" => "",
                "frontend" => "",
                "label" => "Ar Model Type",
                "input" => "select",
                "class" => "",
                "source" => $typeSource,
                "global" => ScopedAttributeInterface::SCOPE_GLOBAL,
                "visible" => true,
                "required" => false,
                "user_defined" => false,
                "default" => "",
                "searchable" => false,
                "filterable" => false,
                "comparable" => false,
                "visible_on_front" => false,
                "used_in_product_listing" => false,
                "unique" => false,
                "sort_order" => 2,
                "apply_to" => "simple,configurable",
            ]
        );

        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            "ar_2d_file",
            [
                "group" => "Mobikul Configuration",
                "type" => "varchar",
                "label" => "AR 2-D Model File",
                "input" => "file",
                "backend" => \Webkul\MobikulCore\Model\Product\Attribute\Backend\Ar::class,
                "frontend" => "",
                "class" => "",
                "source" => "",
                "global" => ScopedAttributeInterface::SCOPE_GLOBAL,
                "visible" => true,
                "required" => false,
                "user_defined" => true,
                "default" => "",
                "searchable" => false,
                "filterable" => false,
                "comparable" => false,
                "visible_on_front" => false,
                "unique" => false,
                "apply_to" => "simple,configurable",
                "used_in_product_listing" => false,
                "sort_order" => 3,
                "note" => "Allowed file type: png."
            ]
        );

        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            "ar_model_file_android",
            [
                "group" => "Mobikul Configuration",
                "type" => "varchar",
                "label" => "AR Model File For Android",
                "input" => "file",
                "backend" => \Webkul\MobikulCore\Model\Product\Attribute\Backend\Ar::class,
                "frontend" => "",
                "class" => "",
                "source" => "",
                "global" => ScopedAttributeInterface::SCOPE_GLOBAL,
                "visible" => true,
                "required" => false,
                "user_defined" => true,
                "default" => "",
                "searchable" => false,
                "filterable" => false,
                "comparable" => false,
                "visible_on_front" => false,
                "unique" => false,
                "apply_to" => "simple,configurable",
                "used_in_product_listing" => false,
                "sort_order" => 4,
                "note" => "Allowed file type: sfb."
            ]
        );

        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            "ar_model_file_ios",
            [
                "group" => "Mobikul Configuration",
                "type" => "varchar",
                "label" => "AR Model File For Ios",
                "input" => "file",
                "backend" => \Webkul\MobikulCore\Model\Product\Attribute\Backend\Ar::class,
                "frontend" => "",
                "class" => "",
                "source" => "",
                "global" => ScopedAttributeInterface::SCOPE_GLOBAL,
                "visible" => true,
                "required" => false,
                "user_defined" => true,
                "default" => "",
                "searchable" => false,
                "filterable" => false,
                "comparable" => false,
                "visible_on_front" => false,
                "unique" => false,
                "apply_to" => "simple,configurable",
                "used_in_product_listing" => false,
                "sort_order" => 5,
                "note" => "Allowed file type: usdz."
            ]
        );
        
        $eavSetup->addAttribute('customer_address', 'address_title', [
            'type' => 'varchar',
            'input' => 'text',
            'label' => 'Address Title',
            'visible' => true,
            'required' => false,
            'user_defined' => true,
            'system'=> false,
            'group'=> 'General',
            'global' => true,
            'visible_on_front' => true,
        ]);
       
        $addressAttribute = $this->eavConfig->getAttribute('customer_address', 'address_title');

        $addressAttribute->setData(
            'used_in_forms',
            ['adminhtml_customer_address','customer_address_edit','customer_register_address']
        );
        $addressAttribute->save();

        $entityType = $this->resourceProduct->getEntityType();
        $attributeSetCollection = $this->attributeSet->setEntityTypeFilter($entityType);
        foreach ($attributeSetCollection as $attributeSet) {
            $attributes = [
                "ar_type",
                "ar_model_file_android",
                "ar_model_file_ios",
                "ar_texture_files"
            ];
            foreach ($attributes as $attribute) {
                $eavSetup->addAttributeToSet(
                    "catalog_product",
                    $attributeSet->getAttributeSetName(),
                    "General",
                    $attribute
                );
            }
        }
    }

    /**
     * Function to move Media to media directory
     *
     * @return void
     */
    protected function moveDirToMediaDir()
    {
        $mediaSplashScreenImageFullPath = $this->fileSystem->getDirectoryRead(
            \Magento\Framework\App\Filesystem\DirectoryList::MEDIA
        )->getAbsolutePath("mobikul/splashscreen");
        if (!$this->filesystemFile->fileExists($mediaSplashScreenImageFullPath)) {
            $this->filesystemFile->mkdir($mediaSplashScreenImageFullPath, 0777, true);
            $splashScreenImage = $this->reader->getModuleDir(
                "",
                "Webkul_MobikulCore"
            )."/view/base/web/images/mobikul/splashscreen/splashscreen.png";
            $this->filesystemFile->cp($splashScreenImage, $mediaSplashScreenImageFullPath."/splashscreen.png");
        }
    }
}
