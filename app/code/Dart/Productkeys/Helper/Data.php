<?php
/**
 * Dart Productkeys Helper
 *
 * @package        Dart_Productkeys
 *
 */
namespace Dart\Productkeys\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;
use Magento\Catalog\Model\ProductFactory ;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Dart\Productkeys\Model\Productkeys;

class Data extends AbstractHelper
{
    const XML_PATH_PRODUCTKEYS = 'productkeys/';

    public function __construct(
        Context $context,
        ProductFactory $productCollection,
        StockRegistryInterface $stockRegistry,
        Productkeys $keysCollection
    ) {
        $this->productCollection = $productCollection;
        $this->stockRegistry = $stockRegistry;
        $this->keysCollection = $keysCollection;
        parent::__construct($context);
    }

    public function getConfigValue($field, $storeId = null)
    {
        return $this->scopeConfig->getValue($field, ScopeInterface::SCOPE_STORE, $storeId);
    }

    public function getGeneralConfig($code, $storeId = null)
    {
        return $this->getConfigValue(self::XML_PATH_PRODUCTKEYS .'general/'. $code, $storeId);
    }

    public function changeQty($keypool)
    {
        $productsUpdated = 0;
        $keyProduct = $this->keysCollection->getCollection()->addFieldToFilter('sku', $keypool)->count();
        if ($keyProduct > 0) {
            $availableKeys = $this->keysCollection->getCollection()
                        ->addFieldToFilter('sku', $keypool)
                        ->addFieldToFilter('status', '0')->count();

            $productBySku = $this->productCollection->create()->loadByAttribute('sku', $keypool);
            $updateQty = $this->getGeneralConfig('productkeys_updatestock');
            if ($productBySku) {
                if ($productBySku->getProductkeyOverwritegnrlconfig()) {
                    $updateQty = $productBySku->getProductkeyUpdatestock();
                }

                if ($updateQty) {
                    $this->setProductQty($keypool, $availableKeys);
                    $productsUpdated++;
                }
            }

            $productsByKeypool = $this->productCollection->create()->getCollection()
                            ->addAttributeToFilter('productkey_pool', $keypool)
                            ->addAttributeToSelect('*');
            foreach ($productsByKeypool as $product) {
                if ($product->getProductkeyOverwritegnrlconfig()) {
                    $updateQty = $product->getProductkeyUpdatestock();
                }

                if ($updateQty) {
                    $this->setProductQty($product->getSku(), $availableKeys);
                    $productsUpdated++;
                }
            }

            return $productsUpdated;
        }
    }

    public function setProductQty($sku, $availableKeys)
    {
        $stockItem = $this->stockRegistry->getStockItemBySku($sku);
        $stockItem->setQty($availableKeys);
        $stockItem->setIsInStock((bool) $availableKeys);
        $this->stockRegistry->updateStockItemBySku($sku, $stockItem);
    }
}
