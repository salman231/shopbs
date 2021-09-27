<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category  Mageplaza
 * @package   Mageplaza_BetterProductReviews
 * @copyright Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license   http://mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\BetterProductReviews\Plugin;

use Exception;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Type\AbstractType;
use Magento\Catalog\Model\ProductFactory;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\DataObject;
use Magento\Framework\Event\Manager;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Pricing\Helper\Data as PriceHelper;
use Magento\Framework\Registry;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Page\Config\Renderer;
use Magento\Review\Model\Review;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\BetterProductReviews\Helper\Data as HelperData;

/**
 * Class SeoReviewRender
 *
 * @package Mageplaza\BetterProductReviews\Plugin
 */
class SeoReviewRender
{
    /**
     * @var Http
     */
    protected $request;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var UrlInterface
     */
    protected $_urlBuilder;

    /**
     * @var ProductFactory
     */
    protected $productFactory;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * @var StockRegistryInterface
     */
    protected $stockState;

    /**
     * @var PriceHelper
     */
    protected $_priceHelper;

    /**
     * @var Manager
     */
    protected $_eventManager;

    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * SeoReviewRender constructor.
     *
     * @param Http $request
     * @param Registry $registry
     * @param StoreManagerInterface $storeManager
     * @param UrlInterface $urlBuilder
     * @param ProductFactory $productFactory
     * @param ManagerInterface $messageManager
     * @param StockRegistryInterface $stockState
     * @param PriceHelper $priceHelper
     * @param Manager $eventManager
     * @param HelperData $helpData
     */
    public function __construct(
        Http $request,
        Registry $registry,
        StoreManagerInterface $storeManager,
        UrlInterface $urlBuilder,
        ProductFactory $productFactory,
        ManagerInterface $messageManager,
        StockRegistryInterface $stockState,
        PriceHelper $priceHelper,
        Manager $eventManager,
        HelperData $helpData
    ) {
        $this->request = $request;
        $this->registry = $registry;
        $this->_storeManager = $storeManager;
        $this->_urlBuilder = $urlBuilder;
        $this->productFactory = $productFactory;
        $this->messageManager = $messageManager;
        $this->stockState = $stockState;
        $this->_priceHelper = $priceHelper;
        $this->_eventManager = $eventManager;
        $this->helperData = $helpData;
    }

    /**
     * @param Renderer $subject
     * @param string $result
     *
     * @return string
     * @SuppressWarnings(Unused)
     */
    public function afterRenderHeadContent(Renderer $subject, $result)
    {
        if ($this->getFullActionName() == 'catalog_product_view') {
            if ($this->helperData->isEnabled() && !$this->helperData->isModuleEnabled('Mageplaza_Seo')) {
                $prodStructureData = $this->showProdStructureData();
                $result = $result . $prodStructureData;
            }
        }

        return $result;
    }

    /**
     * Get full action name
     *
     * @return string
     */
    public function getFullActionName()
    {
        return $this->request->getFullActionName();
    }

    /**
     * Get current product
     *
     * @return Product
     */
    public function getProduct()
    {
        return $this->registry->registry('current_product');
    }

    /**
     * Get Url
     *
     * @param string $route
     * @param array $params
     *
     * @return string
     */
    public function getUrl($route = '', $params = [])
    {
        return $this->_urlBuilder->getUrl($route, $params);
    }

    /**
     * @return array|string
     */
    public function showProdStructureData()
    {
        if ($currentProduct = $this->getProduct()) {
            try {
                $productId = $currentProduct->getId() ?: $this->request->getParam('id');
                /**
                 * @var Product $product
                 */
                $product = $this->productFactory->create()->load($productId);
                $availability = $product->isAvailable() ? 'InStock' : 'OutOfStock';
                $stockItem = $this->stockState->getStockItem(
                    $product->getId(),
                    $product->getStore()->getWebsiteId()
                );
                $priceValidUntil = $product->getSpecialToDate();

                $prodStructureData = [
                    '@context' => 'http://schema.org/',
                    '@type' => 'Product',
                    'name' => $product->getName(),
                    'description' => trim(strip_tags($product->getDescription())),
                    'sku' => $product->getSku(),
                    'url' => $product->getProductUrl(),
                    'image' => $this->getUrl('pub/media/catalog') . 'product' . $product->getImage(),
                    'offers' => [
                        '@type' => 'Offer',
                        'priceCurrency' => $this->_storeManager->getStore()->getCurrentCurrencyCode(),
                        'price' => $product->getPriceInfo()->getPrice('final_price')->getValue(),
                        'itemOffered' => $stockItem->getQty(),
                        'availability' => 'http://schema.org/' . $availability
                    ]
                ];
                $prodStructureData = $this->addProdStructureDataByType(
                    $product->getTypeId(),
                    $product,
                    $prodStructureData
                );

                if (!empty($priceValidUntil)) {
                    $prodStructureData['offers']['priceValidUntil'] = $priceValidUntil;
                }

                if ($this->helperData->getReviewCount($product)) {
                    $prodStructureData['aggregateRating']['@type'] = 'AggregateRating';
                    $prodStructureData['aggregateRating']['bestRating'] = 5;
                    $prodStructureData['aggregateRating']['worstRating'] = 1;
                    $prodStructureData['aggregateRating']['ratingValue'] = number_format(
                        (float)(
                            $this->helperData->getRatingSummary($product) / 20),
                        1
                    );
                    $prodStructureData['aggregateRating']['reviewCount'] = $this->helperData->getReviewCount($product);
                    foreach ($this->helperData->getReviewsCollection($product) as $review) {
                        /**
                         * @var Review $review
                         */
                        $prodStructureData['review'][] =
                            [
                                '@type' => 'Review',
                                'author' => $review->getNickname(),
                                'datePublished' => $review->getCreatedAt(),
                                'description' => $review->getDetail(),
                                'name' => $review->getTitle(),
                                'reviewRating' => [
                                    '@type' => 'Rating',
                                    'bestRating' => 5,
                                    'ratingValue' => $this->helperData->getReviewRatingValue($review),
                                    'worstRating' => 1,
                                ]
                            ];
                    }
                }

                $objectStructuredData = new DataObject(['mp_review_data' => $prodStructureData]);
                $this->_eventManager->dispatch(
                    'mp_productreviews_product_structured_data',
                    ['structured_data' => $objectStructuredData]
                );
                $prodStructureData = $objectStructuredData->getMpReviewData();

                return $this->helperData->createStructuredData(
                    $prodStructureData,
                    '<!-- Product Structured Data by Mageplaza Product Reviews-->'
                );
            } catch (Exception $e) {
                $this->messageManager->addErrorMessage(__('Can not add structured data'));
            }
        }

        return '';
    }

    /**
     * add Grouped Product Structured Data
     *
     * @param AbstractType $currentProduct
     * @param array $prodStructureData
     *
     * @return array
     * @throws NoSuchEntityException
     */
    public function getGroupedProdStructureData($currentProduct, $prodStructureData)
    {
        $prodStructureData['offers']['@type'] = 'AggregateOffer';
        $childrenPrice = [];
        $offerData = [];
        $typeInstance = $currentProduct->getTypeInstance();
        $childProdCol = $typeInstance->getAssociatedProducts($currentProduct);
        foreach ($childProdCol as $child) {
            /**
             * @var Product $child
             */
            $imageUrl = $this->_storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA)
                . 'catalog/product' . $child->getImage();

            $offerData[] = [
                '@type' => "Offer",
                'name' => $child->getName(),
                'price' => $this->_priceHelper->currency($child->getPrice(), false),
                'sku' => $child->getSku(),
                'image' => $imageUrl
            ];
            $childrenPrice[] = $this->_priceHelper->currency($child->getPrice(), false);
        }

        $prodStructureData['offers']['highPrice'] = array_sum($childrenPrice);
        $prodStructureData['offers']['lowPrice'] = min($childrenPrice);
        unset($prodStructureData['offers']['price']);

        if (!empty($offerData)) {
            $prodStructureData['offers']['offers'] = $offerData;
        }

        return $prodStructureData;
    }

    /**
     * add Downloadable Product Structured Data
     *
     * @param Product $currentProduct
     * @param array $prodStructureData
     *
     * @return array
     */
    public function getDownloadableProdStructureData($currentProduct, $prodStructureData)
    {
        $prodStructureData['offers']['@type'] = 'AggregateOffer';

        $typeInstance = $currentProduct->getTypeInstance();
        $childProdCol = $typeInstance->getLinks($currentProduct);
        $childrenPrice = [];
        foreach ($childProdCol as $child) {
            /**
             *
             *
             * @var Product $child
             */
            $offerData[] = [
                '@type' => "Offer",
                'name' => $child->getTitle(),
                'price' => $this->_priceHelper->currency($child->getPrice(), false)
            ];
            $childrenPrice[] = $this->_priceHelper->currency($child->getPrice(), false);
        }
        $prodStructureData['offers']['highPrice'] = array_sum($childrenPrice);
        $prodStructureData['offers']['lowPrice'] = min($childrenPrice);

        if (!empty($offerData)) {
            $prodStructureData['offers']['offers'] = $offerData;
        }

        return $prodStructureData;
    }

    /**
     * add Configurable Product Structured Data
     *
     * @param Product $currentProduct
     * @param array $prodStructureData
     *
     * @return array
     * @throws NoSuchEntityException
     */
    public function getConfigurableProdStructureData($currentProduct, $prodStructureData)
    {
        $prodStructureData['offers']['@type'] = 'AggregateOffer';
        $prodStructureData['offers']['highPrice'] = $currentProduct
            ->getPriceInfo()->getPrice('regular_price')->getMaxRegularAmount()->getValue();
        $prodStructureData['offers']['lowPrice'] = $currentProduct
            ->getPriceInfo()->getPrice('regular_price')->getMinRegularAmount()->getValue();
        $offerData = [];
        $typeInstance = $currentProduct->getTypeInstance();
        $childProdCol = $typeInstance
            ->getUsedProductCollection($currentProduct)->addAttributeToSelect('*');
        foreach ($childProdCol as $child) {
            /**
             * @var Product $child
             */
            $imageUrl = $this->_storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA)
                . 'catalog/product' . $child->getImage();

            $offerData[] = [
                '@type' => "Offer",
                'name' => $child->getName(),
                'price' => $this->_priceHelper->currency($child->getPrice(), false),
                'sku' => $child->getSku(),
                'image' => $imageUrl
            ];
        }
        if (!empty($offerData)) {
            $prodStructureData['offers']['offers'] = $offerData;
        }

        return $prodStructureData;
    }

    /**
     * add Bundle Product Structured Data
     *
     * @param Product $currentProduct
     * @param array $prodStructureData
     *
     * @return array
     * @throws NoSuchEntityException
     */
    public function getBundleProdStructureData($currentProduct, $prodStructureData)
    {
        $prodStructureData['offers']['@type'] = 'AggregateOffer';
        $prodStructureData['offers']['highPrice'] = $currentProduct
            ->getPriceInfo()->getPrice('regular_price')->getMaximalPrice()->getValue();
        $prodStructureData['offers']['lowPrice'] = $currentProduct
            ->getPriceInfo()->getPrice('regular_price')->getMinimalPrice()->getValue();
        unset($prodStructureData['offers']['price']);
        $offerData = [];
        $typeInstance = $currentProduct->getTypeInstance();
        $childProdCol = $typeInstance
            ->getSelectionsCollection(
                $typeInstance->getOptionsIds($currentProduct),
                $currentProduct
            );
        foreach ($childProdCol as $child) {
            /**
             *
             *
             * @var Product $child
             */
            $imageUrl = $this->_storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA)
                . 'catalog/product' . $child->getImage();

            $offerData[] = [
                '@type' => "Offer",
                'name' => $child->getName(),
                'price' => $this->_priceHelper->currency($child->getPrice(), false),
                'sku' => $child->getSku(),
                'image' => $imageUrl
            ];
        }
        if (!empty($offerData)) {
            $prodStructureData['offers']['offers'] = $offerData;
        }

        return $prodStructureData;
    }

    /**
     * add Product Structured Data By Type
     *
     * @param $productType
     * @param $currentProduct
     * @param $prodStructureData
     *
     * @return array
     * @throws NoSuchEntityException
     */
    public function addProdStructureDataByType($productType, $currentProduct, $prodStructureData)
    {
        switch ($productType) {
            case 'grouped':
                $prodStructureData = $this->getGroupedProdStructureData($currentProduct, $prodStructureData);
                break;
            case 'bundle':
                $prodStructureData = $this->getBundleProdStructureData($currentProduct, $prodStructureData);
                break;
            case 'downloadable':
                $prodStructureData = $this->getDownloadableProdStructureData($currentProduct, $prodStructureData);
                break;
            case 'configurable':
                $prodStructureData = $this->getConfigurableProdStructureData($currentProduct, $prodStructureData);
                break;
        }

        return $prodStructureData;
    }
}
