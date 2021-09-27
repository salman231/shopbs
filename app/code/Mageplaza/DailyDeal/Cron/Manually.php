<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category  Mageplaza
 * @package   Mageplaza_DailyDeal
 * @copyright Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license   https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\DailyDeal\Cron;

use Exception;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\DailyDeal\Helper\Data as HelperData;
use Mageplaza\DailyDeal\Model\Config\Source\Status;
use Mageplaza\DailyDeal\Model\DealFactory;

/**
 * Class Manually
 * @package Mageplaza\DailyDeal\Cron
 */
class Manually
{
    /**
     * Maximum number of random random products
     *
     * @var int
     */
    protected $_count = 0;

    /**
     * @var HelperData
     */
    public $_helperData;

    /**
     * @var DealFactory
     */
    protected $_dealFactory;

    /**
     * @var CategoryFactory
     */
    protected $_categoryFactory;

    /**
     * @var ProductRepository
     */
    protected $productRepository;

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * Manually constructor.
     *
     * @param HelperData $helperData
     * @param DealFactory $dealFactory
     * @param CategoryFactory $categoryFactory
     * @param ProductRepository $productRepository
     * @param StoreManagerInterface $storeManager
     * @param RequestInterface $request
     */
    public function __construct(
        HelperData $helperData,
        DealFactory $dealFactory,
        CategoryFactory $categoryFactory,
        ProductRepository $productRepository,
        StoreManagerInterface $storeManager,
        RequestInterface $request
    ) {
        $this->request           = $request;
        $this->_helperData       = $helperData;
        $this->_dealFactory      = $dealFactory;
        $this->_categoryFactory  = $categoryFactory;
        $this->productRepository = $productRepository;
        $this->_storeManager     = $storeManager;
    }

    /**
     * @throws NoSuchEntityException
     * @throws Exception
     */
    public function process()
    {
        $storeId             = (int) $this->request->getParam('store'); //if cron run then get default store id
        $numOfRandomProducts = $this->getNumberProductCreated();

        for ($i = 0; $i < $numOfRandomProducts; $i++) {
            $product = $this->getProductDealCreated($storeId);

            $now    = date('Y-m-d');
            $toTime = strtotime('+' . $this->getTimeDealCreated() . 'day', strtotime($now));

            $data = [
                'product_id'   => $product->getId(),
                'product_name' => $product->getName(),
                'product_sku'  => $product->getSku(),
                'status'       => Status::ACTIVE,
                'is_featured'  => 1,
                'deal_price'   => $this->getPriceDealCreated($product->getPrice()),
                'deal_qty'     => $this->getQtyDealCreated(),
                'sale_qty'     => 0,
                'store_ids'    => $storeId,
                'date_from'    => $now,
                'date_to'      => date('Y-m-d', $toTime)
            ];

            $this->_dealFactory->create()->addData($data)->save();
        }
    }

    /**
     * @param int $catId
     * @param $storeId
     *
     * @return Category
     */
    public function getCategory($catId, $storeId)
    {
        return $this->_categoryFactory->create()->setStoreId($storeId)->load($catId);
    }

    /**
     * Get random product
     *
     * @param $storeId
     *
     * @return ProductInterface|mixed
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function getProductDealCreated($storeId)
    {
        $randomProduct   = $this->getRandomProduct($storeId);
        $randomProductId = $randomProduct->getId();

        /** check if product has been exists in list deal or stock item greater than deal qty */
        if ($this->isProductAlreadyInDeal($randomProduct)) {
            $stockItemQty = (int) $this->_helperData->getProductQtyByProductId($randomProductId);
            $dealQty      = (int) $this->getQtyDealCreated();

            if ($stockItemQty >= $dealQty) {
                return $randomProduct;
            }

            return $this->getProductDealCreated($storeId);
        }

        $this->_count++;
        if ($this->_count > 1000) {
            throw new LocalizedException(
                __('There are no satisfying products in this category.')
            );
        }

        return $this->getProductDealCreated($storeId);
    }

    /**
     * Get Price deal created
     *
     * @param float $basePrice
     *
     * @return float|int
     */
    public function getPriceDealCreated($basePrice)
    {
        $percent = $this->_helperData->getModuleConfig('create_deal/percent_price');

        return $basePrice - $basePrice * $percent / 100;
    }

    /**
     * Get deal qty created
     *
     * @return int
     */
    public function getQtyDealCreated()
    {
        $qty = $this->_helperData->getModuleConfig('create_deal/qty');

        return $qty ?: 10;
    }

    /**
     * Get day deal created
     *
     * @return int
     */
    public function getTimeDealCreated()
    {
        $config = $this->_helperData->getModuleConfig('create_deal/day');

        return $config ?: 1;
    }

    /**
     * get number of products generated per cron run
     *
     * @return int
     */
    public function getNumberProductCreated()
    {
        $config = $this->_helperData->getModuleConfig('create_deal/product_number');

        return $config ?: 1;
    }

    /**
     * @param Product $product
     *
     * @return bool
     */
    private function isProductAlreadyInDeal(Product $product)
    {
        $dealCollection = $this->_dealFactory->create()->getCollection()
            ->addFieldToSelect('product_id')
            ->addFieldToFilter('product_id', ['eq' => $product->getEntityId()]);

        return $dealCollection->getSize() === 0;
    }

    /**
     * Get random product id from category
     *
     * @param $store
     *
     * @return mixed
     */
    private function getRandomProduct($store)
    {
        $categories  = $this->_helperData->getCategory($store);
        $random_key  = array_rand($categories, 1);
        $catIdRandom = $categories[$random_key];
        $productsCol = $this->getCategory($catIdRandom, $store)
            ->getProductCollection()->addAttributeToSelect(['name', 'sku', 'price'])
            ->setPageSize(1);

        $productsCol->getSelect()->orderRand();
        $randomProduct = $productsCol->getFirstItem();

        if ($randomProduct->getTypeId() === 'configurable') {
            $childrenProducts = $randomProduct->getTypeInstance()->getUsedProducts($randomProduct);
            $random_key       = array_rand($childrenProducts, 1);
            $randomProduct    = $childrenProducts[$random_key];
        }

        return $randomProduct->getEntityId() ? $randomProduct : $this->getRandomProduct($store);
    }
}
