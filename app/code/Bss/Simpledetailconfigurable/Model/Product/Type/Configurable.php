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
 * @category  BSS
 * @package   Bss_Simpledetailconfigurable
 * @author    Extension Team
 * @copyright Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license   http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\Simpledetailconfigurable\Model\Product\Type;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as MagentoConfigurable;

/**
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class Configurable extends MagentoConfigurable
{
    /**
     * @var Config
     */
    protected $catalogConfig;

    /**
     * Configurable construct
     */
    protected function _construct()
    {
        $this->start();
    }

    /**
     * @return Configurable $this
     */
    public function start()
    {
        return $this;
    }

    /**
     * Returns array of sub-products for specified configurable product
     * Result array contains all children for specified configurable product
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param array $requiredAttributeIds Attributes to include in the select; one-dimensional array
     * @return ProductInterface[]
     */
    public function getUsedProducts($product, $requiredAttributeIds = null)
    {
        if (!$product->hasData($this->_usedProducts)) {
            $collection = $this->getConfiguredUsedProductCollection($product, false, $requiredAttributeIds);
            $usedProducts = array_values($collection->getItems());
            $product->setData($this->_usedProducts, $usedProducts);
        }

        return $product->getData($this->_usedProducts);
    }

    /**
     * Retrieve related products collection
     *
     * @param  \Magento\Catalog\Model\Product $product
     * @return \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable\Product\Collection
     */
    public function getUsedProductCollection($product)
    {
        $collection = $this->_productCollectionFactory->create()->setFlag(
            'product_children',
            true
        )->setProductFilter(
            $product
        );
        if (null !== $this->getStoreFilter($product)) {
            $collection->addStoreFilter($this->getStoreFilter($product));
        }
        if ($this->start()->getModuleConfig()->isModuleEnable()
            && !$this->start()->getModuleConfig()->isEnableChildOption()) {
            $collection
                ->addFilterByRequiredOptions()
                ->addAttributeToFilter('has_options', [['neq' => 1], ['null' => true]], 'left');
        } elseif (!$this->start()->getModuleConfig()->isModuleEnable()) {
            $collection->addFilterByRequiredOptions();
        }

        return $collection;
    }

    /**
     * Prepare collection for retrieving sub-products of specified configurable product
     * Retrieve related products collection with additional configuration
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param bool $skipStockFilter
     * @param array $requiredAttributeIds Attributes to include in the select
     * @return \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable\Product\Collection
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function getConfiguredUsedProductCollection(
        \Magento\Catalog\Model\Product $product,
        $skipStockFilter = true,
        $requiredAttributeIds = null
    ) {
        $collection = $this->getUsedProductCollection($product);
        if ($skipStockFilter) {
            $collection->setFlag('has_stock_status_filter', true);
        }

        $attributesForSelect = $this->getAttributesForCollection($product);

        if (version_compare($this->start()->GetVersion(), '2.3.3', '>')){
            if ($requiredAttributeIds) {
                $this->start()->getSearchCriteriaBuilder()->addFilter('attribute_id', $requiredAttributeIds, 'in');
                $requiredAttributes = $this->start()->getProductAttributeRepository()
                    ->getList($this->start()->getSearchCriteriaBuilder()->create())->getItems();
                $requiredAttributeCodes = [];
                foreach ($requiredAttributes as $requiredAttribute) {
                    $requiredAttributeCodes[] = $requiredAttribute->getAttributeCode();
                }
                $attributesForSelect = array_unique(array_merge($attributesForSelect, $requiredAttributeCodes));
            }
        }

        $collection->addAttributeToSelect($attributesForSelect);

        $collection->setStoreId($product->getStoreId());

        $collection->addMediaGalleryData();
        $collection->addTierPriceData();

        return $collection;
    }

    /**
     * Get Config instance
     */
    protected function getCatalogConfig()
    {
        if (!$this->catalogConfig) {
            $this->catalogConfig = $this->start()->getDataCatalogConfig();
        }
        return $this->catalogConfig;
    }

    /**
     * @return array
     */
    protected function getAttributesForCollection(\Magento\Catalog\Model\Product $product)
    {
        $productAttributes = $this->getCatalogConfig()->getProductAttributes();

        $requiredAttributes = [
            'name',
            'price',
            'weight',
            'image',
            'thumbnail',
            'status',
            'visibility',
            'media_gallery'
        ];

        $usedAttributes = array_map(
            function ($attr) {
                return $attr->getAttributeCode();
            },
            $this->getUsedProductAttributes($product)
        );

        return array_unique(array_merge($productAttributes, $requiredAttributes, $usedAttributes));
    }
}
