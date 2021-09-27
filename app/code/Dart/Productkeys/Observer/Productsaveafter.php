<?php
/**
 * Dart Productkeys Product Save Observer
 *
 * @package        Dart_Productkeys
 *
 */
namespace Dart\Productkeys\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Message\ManagerInterface;
use Dart\Productkeys\Model\Productkeys;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Dart\Productkeys\Helper\Data;

class Productsaveafter implements ObserverInterface
{
    public function __construct(
        ManagerInterface $messageManager,
        Productkeys $keysCollection,
        StockRegistryInterface $stockRegistry,
        Data $helperData
    ) {
        $this->messageManager = $messageManager;
        $this->keysCollection = $keysCollection;
        $this->stockRegistry = $stockRegistry;
        $this->helperData = $helperData;
    }

    public function execute(Observer $observer)
    {
        /* $product = $observer->getProduct();

        if ($product->getProductkeyOverwritegnrlconfig()) {
            $updateStock = $product->getProductkeyUpdatestock();
        } else {
            $updateStock = $this->helperData->getGeneralConfig('productkeys_updatestock');
        }

        if ($updateStock) {
            $keypool = $product->getProductkeyPool();

            if (empty($keypool)) {
                $keypool = $product->getSku();
            }

            $keyProduct = $this->keysCollection->getCollection()->addFieldToFilter('sku', $keypool)->count();
            if ($keyProduct > 0) {
                $availableKeys = $this->keysCollection->getCollection()
                            ->addFieldToFilter('sku', $keypool)
                            ->addFieldToFilter('status', '0')->count();
				//$product->setStockData(['qty' => $availableKeys, 'is_in_stock' => (bool) $availableKeys]);
				//$this->helperData->setProductQty($product->getSku(), $availableKeys);
                $prdMessage = __('Product quantity has been updated according to Keypool "%1" availability.', $keypool);
                $this->messageManager->addSuccess($prdMessage);
            }
        } */
    }
}
