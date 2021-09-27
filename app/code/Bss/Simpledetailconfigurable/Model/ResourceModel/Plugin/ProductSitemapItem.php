<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_Simpledetailconfigurable
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\Simpledetailconfigurable\Model\ResourceModel\Plugin;

use Magento\Catalog\Model\Product;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ProductSitemapItem
{
    /**
     * @var array
     */
    protected $attributeList = [];

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var \Magento\ConfigurableProduct\Model\Product\Type\Configurable
     */
    protected $configurableType;

    /**
     * @var \Bss\Simpledetailconfigurable\Helper\ModuleConfig
     */
    protected $configHelper;

    /**
     * @var \Bss\Simpledetailconfigurable\Model\ResourceModel\Catalog\Product
     */
    protected $sitemapProduct;

    /**
     * @var \Magento\Framework\DataObjectFactory
     */
    protected $dataObjectFactory;

    /**
     * ProductSitemapItem constructor.
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $collectionFactory
     * @param \Magento\ConfigurableProduct\Model\Product\Type\Configurable $configurableType
     * @param \Bss\Simpledetailconfigurable\Helper\ModuleConfig $configHelper
     * @param \Bss\Simpledetailconfigurable\Model\ResourceModel\Catalog\Product $sitemapProduct
     * @param \Magento\Framework\DataObjectFactory $dataObjectFactory
     */
    public function __construct(
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $collectionFactory,
        \Magento\ConfigurableProduct\Model\Product\Type\Configurable $configurableType,
        \Bss\Simpledetailconfigurable\Helper\ModuleConfig $configHelper,
        \Bss\Simpledetailconfigurable\Model\ResourceModel\Catalog\Product $sitemapProduct,
        \Magento\Framework\DataObjectFactory $dataObjectFactory
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->configurableType = $configurableType;
        $this->configHelper = $configHelper;
        $this->sitemapProduct = $sitemapProduct;
        $this->dataObjectFactory = $dataObjectFactory;
    }

    /**
     * Add child product to xml site map
     *
     * @param \Magento\Sitemap\Model\ResourceModel\Catalog\Product $subject
     * @param \Closure $proceed
     * @param string $storeId
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function aroundGetCollection(
        \Magento\Sitemap\Model\ResourceModel\Catalog\Product $subject,
        \Closure $proceed,
        $storeId
    ) {
        $products = $proceed($storeId);
        if (!$this->configHelper->isUseForXmlSitemap($storeId)) {
            return $products;
        }
        $connection = $subject->getConnection();
        $select = $connection->select()->from($subject->getMainTable(), 'entity_id')
            ->where('type_id = ?', 'configurable');
        $configurableIds = $connection->fetchCol($select);
        $configurableProducts = $this->collectionFactory->create()->addIdFilter($configurableIds);
        $childProductList = [];
        foreach ($configurableProducts as $configurableProduct) {
            $attributeConfig = $this->getConfigurableAttribtues($configurableProduct);
            $allChildExtendsUrls = $this->getAllChildExtendsUrls($attributeConfig, $configurableProduct);
            $childProductList[$configurableProduct->getId()] = $allChildExtendsUrls;
        }
        $newProducts = $products;
        $rowId = count($products);
        foreach ($products as $product) {
            if (isset($childProductList[$product->getId()])) {
                foreach ($childProductList[$product->getId()] as $extendUrl) {
                    $productRow = $product->getData();
                    if (strpos($productRow['url'], '.html') !== false) {
                        $productRow['url'] = str_replace('.html', $extendUrl, $productRow['url']);
                    } else {
                        $productRow['url'] = $productRow['url'] . $extendUrl;
                    }
                    $productRow[$this->sitemapProduct->getIdFieldName()] = $rowId;
                    $childProduct = $this->sitemapProduct->_prepareProductCustom(
                        $productRow,
                        $storeId,
                        $this->returnNewObjectProduct()
                    );
                    $newProducts[$rowId] = $childProduct;
                    $rowId++;
                }
            }
        }
        return $newProducts;
    }

    /**
     * @return \Magento\Framework\DataObject
     */
    protected function returnNewObjectProduct()
    {
        return $this->dataObjectFactory->create();
    }

    /**
     * Get attributes of configurable product
     *
     * @param Product $configurableProduct
     * @return array
     */
    protected function getConfigurableAttribtues($configurableProduct)
    {
        $attributeConfig = [];
        foreach ($this->configurableType->getConfigurableAttributesAsArray($configurableProduct) as $attribute) {
            foreach ($attribute['values'] as $item) {
                $storeLabel = $item['store_label'];
                $storeLabel = preg_replace('/[`!@#$%^&*()_|\;:\",.<>\{\}\[\]\/]/', '', $storeLabel);
                $storeLabel = preg_replace('/"/', '', $storeLabel);
                $storeLabel = preg_replace("/'/", '', $storeLabel);
                $storeLabel = trim($storeLabel);
                $storeLabel = preg_replace('/ /', '~', $storeLabel);
                $attributeConfig[$attribute['attribute_code']][$item['value_index']] = $storeLabel;
            }
        }
        return $attributeConfig;
    }

    /**
     * @param array $attributeConfig
     * @param Product $product
     * @return array
     */
    protected function getAllChildExtendsUrls($attributeConfig, $product)
    {
        $result = [];
        $childProducts = $this->configurableType->getUsedProducts($product);
        foreach ($childProducts as $childProduct) {
            $productData = $childProduct->getData();
            $result[$childProduct->getId()] = '';
            foreach ($attributeConfig as $attributeCode => $attribute) {
                if (isset($productData[$attributeCode]) && isset($attribute[$productData[$attributeCode]])) {
                    $result[$childProduct->getId()] .= '+' . $attributeCode;
                    $result[$childProduct->getId()] .= '-' . $attribute[$productData[$attributeCode]];
                }
            }
        }
        return $result;
    }
}
