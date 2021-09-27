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

namespace Bss\Simpledetailconfigurable\Helper;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\ScopeInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessiveParameterList)
 */
class ProductData extends AbstractHelper
{
    /**
     * @var \Magento\CatalogInventory\Model\StockRegistry
     */
    private $stockRegistry;

    /**
     * @var \Magento\Catalog\Helper\Image
     */
    private $imageHelper;

    /**
     * @var \Bss\Simpledetailconfigurable\Model\PreselectKeyFactory
     */
    private $preselectKey;

    /**
     * @var \Bss\Simpledetailconfigurable\Model\ProductEnabledModuleFactory
     */
    private $productEnabledModule;

    /**
     * @var \Magento\Cms\Model\Template\FilterProvider
     */
    private $filterProvider;

    /**
     * @var \Bss\Simpledetailconfigurable\Helper\ModuleConfig
     */
    private $moduleConfig;

    /**
     * @var \Bss\Simpledetailconfigurable\Model\ResourceModel\SourceStock\SalesChannel
     */
    private $salesChannel;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var \Magento\Review\Model\ResourceModel\Review\CollectionFactory
     */
    protected $_reviewsColFactory;

    /**
     * ProductData constructor.
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\CatalogInventory\Model\StockRegistry $stockRegistry
     * @param \Magento\Catalog\Helper\Image $imageHelper
     * @param \Bss\Simpledetailconfigurable\Model\ProductEnabledModuleFactory $productEnabledModule
     * @param \Bss\Simpledetailconfigurable\Model\PreselectKeyFactory $preselectKey
     * @param \Magento\Cms\Model\Template\FilterProvider $filterProvider
     * @param \Bss\Simpledetailconfigurable\Helper\ModuleConfig $moduleConfig
     * @param \Bss\Simpledetailconfigurable\Model\ResourceModel\SourceStock\SalesChannel $salesChannel
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\Review\Model\ResourceModel\Review\CollectionFactory $_reviewsColFactory
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\CatalogInventory\Model\StockRegistry $stockRegistry,
        \Magento\Catalog\Helper\Image $imageHelper,
        \Bss\Simpledetailconfigurable\Model\ProductEnabledModuleFactory $productEnabledModule,
        \Bss\Simpledetailconfigurable\Model\PreselectKeyFactory $preselectKey,
        \Magento\Cms\Model\Template\FilterProvider $filterProvider,
        \Bss\Simpledetailconfigurable\Helper\ModuleConfig $moduleConfig,
        \Bss\Simpledetailconfigurable\Model\ResourceModel\SourceStock\SalesChannel $salesChannel,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Review\Model\ResourceModel\Review\CollectionFactory $_reviewsColFactory,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->stockRegistry = $stockRegistry;
        $this->imageHelper = $imageHelper;
        $this->preselectKey = $preselectKey;
        $this->productEnabledModule = $productEnabledModule;
        $this->filterProvider = $filterProvider;
        $this->moduleConfig = $moduleConfig;
        $this->salesChannel = $salesChannel;
        $this->productRepository = $productRepository;
        $this->_reviewsColFactory = $_reviewsColFactory;
        parent::__construct($context);
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @return \Bss\Simpledetailconfigurable\Model\PreselectKeyFactory
     */
    public function getPreselectKey()
    {
        return $this->preselectKey;
    }

    /**
     * @return ModuleConfig
     */
    public function getModuleConfig()
    {
        return $this->moduleConfig;
    }

    /**
     * @return \Magento\Framework\Event\ManagerInterface
     */
    public function getEventManager()
    {
        return $this->_eventManager;
    }

    /**
     * @param int $productEntityId
     * @return array
     * @throws NoSuchEntityException
     */
    public function getAllProductName($productEntityId)
    {
        $result = [];
        $product = $this->productRepository->getById($productEntityId);
        $parentProduct = $product->getTypeInstance()->getUsedProducts($product);
        $parentAttribute = $product->getTypeInstance()->getConfigurableAttributes($product);
        foreach ($parentProduct as $simpleProduct) {
            $key = '';
            foreach ($parentAttribute as $attrValue) {
                $attrLabel = $attrValue->getProductAttribute()->getAttributeCode();
                $key .= $simpleProduct->getData($attrLabel) . '_';
            }
            $result['child'][$key] = [
                'id' => $simpleProduct->getId(),
                'name' => $simpleProduct->getName()
            ];
        }
        $result['child']['default'] = $product->getName();

        return $result;
    }

    /**
     * @param int $productEntityId
     * @return array
     * @throws NoSuchEntityException
     */
    public function getAllData($productEntityId)
    {
        $result = [];
        $product = $this->productRepository->getById($productEntityId);
        $result = $this->getDetailData($product);
        $isAjaxLoad = $this->getEnabledModuleOnProduct($productEntityId)['is_ajax_load'];
        $result['is_ajax_load'] = $isAjaxLoad;
        $result['preselect'] = $this->getSelectingDataWithConfig($productEntityId);
        $this->getDetailStock($result, false);
        $result['url'] = $product->getProductUrl();
        if ($result['url'] == null) {
            $result['url'] = str_replace(' ', '-', $result['name']) . $this->moduleConfig->getSuffix();
        }

        if ($isAjaxLoad) {
            $this->getDetailPrice($product, $result);
            $result['child'] = [];
            return $result;
        }
        $parentPrice = 0;
        $options = $product->getTypeInstance()->getUsedProducts($product);
        foreach ($options as $simpleProduct) {
            $childProduct = $this->getChildDetail($simpleProduct);
            $result['child'][$simpleProduct->getId()] = $childProduct;
            $parentPrice = $childProduct['price']['finalPrice'];
        }
        foreach ($result['child'] as $ri) {
            $parentPrice = ($ri['price']['finalPrice'] < $parentPrice) ? $ri['price']['finalPrice'] : $parentPrice;
        }
        $result['price']['finalPrice'] = $parentPrice;
        return $result;
    }

    /**
     * @param \Magento\Catalog\Model\Product|string $child
     * @return array|bool
     * @throws \Exception
     */
    public function getChildDetail($child)
    {
        if ($child instanceof \Magento\Catalog\Model\Product) {
            $id = $child->getId();
        } else {
            $id = $child;
        }
        try {
            // Reload child product additional data and meta data
            $childProduct = $this->productRepository->getById($id);
            $result = $this->getDetailData($childProduct);
            $this->getDetailStock($result);
            $this->getDetailPrice($childProduct, $result);
            return $result;
        } catch (NoSuchEntityException $e) {
            return false;
        }
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @return array
     * @throws \Exception
     */
    public function getDetailData($product)
    {
        $data = [];
        $productDesc = $product->getDescription() ?: '';
        $productShortDesc = $product->getShortDescription() ?: '';
        $data['entity'] = $product->getId();
        $data['sku'] = $product->getSku();
        $data['name'] = $product->getName();
        $data['desc'] = $this->filterProvider->getPageFilter()->filter($productDesc);
        $data['sdesc'] = $this->filterProvider->getPageFilter()->filter($productShortDesc);
        $data['meta_data']['meta_title'] = $product->getMetaTitle();
        $data['meta_data']['meta_keyword'] = $product->getMetaKeyword();
        $data['meta_data']['meta_description'] = $product->getMetaDescription();
        $data['additional_info'] = $this->getAdditionalInfo($product);
        $data['image'] = $this->getGalleryImages($product);
        $data['has_custom_options'] = $product->hasCustomOptions();
        $data['review_count'] = $this->getCollectionReviewSize($product);
        if (version_compare($this->moduleConfig->getMagentoVersion(), '2.2.0', '<')) {
            $data['video'] = $this->getVideoData($product);
        }
        return $data;
    }

    /**
     * Get size of reviews collection
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return int
     */
    public function getCollectionReviewSize($product)
    {
        $collection = $this->_reviewsColFactory->create()->addStoreFilter(
            $this->moduleConfig->getStoreManager()->getStore()->getId()
        )->addStatusFilter(
            \Magento\Review\Model\Review::STATUS_APPROVED
        )->addEntityFilter(
            'product',
            $product->getId()
        );

        return $collection->getSize();
    }

    /**
     * @param array $data
     * @param bool $isChild
     */
    public function getDetailStock(&$data, $isChild = true)
    {
        $childStock = $this->stockRegistry->getStockItem($data['entity']);
        $data['stock_number'] = $childStock->getQty();
        $data['stock_status'] = $childStock->getIsInStock();
        if ($isChild) {
            if ($this->moduleConfig->isEnableMsi()) {
                $websiteCode = $this->moduleConfig->getWebsiteCode();
                $stockId = $this->salesChannel->getStockIdByWebsiteCode($websiteCode);
                $detailStock = $this->salesChannel->getStockDetail($stockId, $data['sku']);
                $data['stock_number'] = 0;
                $data['stock_status'] = 0;
                if ($detailStock) {
                    $data['stock_number'] = $detailStock['quantity'];
                    $data['stock_status'] = $detailStock['is_salable'];
                }
            }

            $data['minqty'] = ($childStock->getUseConfigMinSaleQty()) ? 0 : $childStock->getMinSaleQty();
            $data['maxqty'] = ($childStock->getUseConfigMaxSaleQty()) ? 0 : $childStock->getMaxSaleQty();
            $data['increment'] = ($childStock->getUseConfigQtyIncrements()) ? 0 : $childStock->getQtyIncrements();
        }
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @param array $data
     */
    public function getDetailPrice($product, &$data)
    {
        $data['price']['oldPrice'] = $product->getPriceInfo()->getPrice('regular_price')->getAmount()->getValue();
        $data['price']['basePrice'] = $product->getPriceInfo()->getPrice('final_price')->getAmount()->getBaseAmount();
        $data['price']['finalPrice'] = $product->getPriceInfo()->getPrice('final_price')->getAmount()->getValue();
        $data['price']['tier_price'] = $this->getTierPriceData($product);
    }

    /**
     * @param int $productId
     * @return array
     * @throws NoSuchEntityException
     */
    public function getSelectingKey($productId)
    {
        $result = [];
        $product = $this->productRepository->getById($productId);
        $parentProduct = $product->getTypeInstance()->getUsedProducts($product);
        $parentAttribute = $product->getTypeInstance()->getConfigurableAttributes($product);
        foreach ($parentProduct as $child) {
            foreach ($parentAttribute as $attrValue) {
                $attrLabel = $attrValue->getProductAttribute()->getAttributeCode();
                if (!array_key_exists($attrLabel, $child->getAttributes())) {
                    continue;
                }
                $result[$attrValue->getAttributeId()]['label'] = $attrValue->getLabel();
                $childRow = $child->getAttributes()[$attrLabel]->getFrontend()->getValue($child);
                $result[$attrValue->getAttributeId()]['child'][$child->getData($attrLabel)] = $childRow;
            }
        }
        return $result;
    }

    /**
     * @param int $productId
     * @return array
     */
    public function getSelectingData($productId)
    {
        return $this->preselectKey->create()->getCollection()->getArrayData($productId);
    }

    /**
     * @param int $product
     * @return array
     */
    public function getSelectingDataWithConfig($product)
    {
        $result = [];
        $result['data'] = $this->getSelectingData($product);
        if (isset($result['data'])) {
            $result['enabled'] = true;
        } else {
            $result['enabled'] = false;
        }
        return $result;
    }

    /**
     * @param string $productId
     * @return \Bss\Simpledetailconfigurable\Model\ProductEnabledModule
     */
    public function getEnabledModuleOnProduct($productId)
    {
        $resultObject = $this->productEnabledModule->create()->load($productId);
        if (!$resultObject->getProductId()) {
            return $this->productEnabledModule->create()->setData(['enabled' => 1, 'is_ajax_load' => 0]);
        }
        return $resultObject;
    }

    /**
     * @param string $productId
     * @return bool
     */
    public function isAjaxLoad($productId)
    {
        $resultObject = $this->productEnabledModule->create()->load($productId);
        if (!$resultObject->getProductId()) {
            return false;
        }
        return $resultObject->getIsAjaxLoad();
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @return array
     */
    public function getTierPriceData($product)
    {
        $result = [];
        $finalPrice = $product->getPriceInfo()->getPrice('final_price')->getAmount()->getValue();
        $baseFinalPrice = $product->getPriceInfo()->getPrice('final_price')->getAmount()->getBaseAmount();
        $tierPricesList = $product->getPriceInfo()->getPrice('tier_price')->getTierPriceList();
        if (isset($tierPricesList) && !empty($tierPricesList)) {
            foreach ($tierPricesList as $tier) {
                $tierData = [];
                $tierData['qty'] = $tier['price_qty'];
                $tierData['final'] = $tier['price']->getValue();
                $tierData['value'] = $tier['price']->getValue();
                $tierData['base'] = $tier['price']->getBaseAmount();
                $tierData['final_discount'] = $tierData['final'] - $finalPrice;
                $tierData['base_discount'] = $tierData['base'] - $baseFinalPrice;
                $tierData['percent'] = (1 - $tierData['base'] / $baseFinalPrice) * 100;
                $result[$tierData['qty']] = $tierData;
            }
        }
        return $result;
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @return mixed
     */
    public function getMainImage($product)
    {
        $images = $product->getMediaGallery('images');
        return $images;
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @return array
     * @throws NoSuchEntityException
     */
    public function getGalleryImages($product)
    {
        $images = $product->getMediaGalleryImages();
        $imagesItems = [];
        if ($images instanceof \Magento\Framework\Data\Collection) {
            foreach ($images as $image) {
                $image->setData(
                    'small_image_url',
                    $this->imageHelper->init($product, 'product_page_image_small')
                        ->setImageFile($image->getFile())
                        ->getUrl()
                );
                $image->setData(
                    'medium_image_url',
                    $this->imageHelper->init($product, 'product_page_image_medium')
                        ->constrainOnly(true)->keepAspectRatio(true)->keepFrame(false)
                        ->setImageFile($image->getFile())
                        ->getUrl()
                );
                $image->setData(
                    'large_image_url',
                    $this->imageHelper->init($product, 'product_page_image_large')
                        ->constrainOnly(true)->keepAspectRatio(true)->keepFrame(false)
                        ->setImageFile($image->getFile())
                        ->getUrl()
                );
                $imagesItems[] = [
                    'thumb' => $image->getData('small_image_url'),
                    'img' => $image->getData('medium_image_url'),
                    'full' => $image->getData('large_image_url'),
                    'caption' => ($image->getLabel() ?: $product->getName()),
                    'position' => $image->getPosition(),
                    'isMain' => $product->getImage() == $image->getFile(),
                    'type' => str_replace('external-', '', $image->getMediaType()),
                    'videoUrl' => $image->getVideoUrl(),
                ];
            }
        }
        if (empty($imagesItems) && $this->moduleConfig->getChildImageConfig() === 'placeholder') {
            $imagesItems[] = [
                'thumb' => $this->imageHelper->getDefaultPlaceholderUrl('thumbnail'),
                'img' => $this->imageHelper->getDefaultPlaceholderUrl('image'),
                'full' => $this->imageHelper->getDefaultPlaceholderUrl('image'),
                'type' => 'image',
                'videoUrl' => null,
                'caption' => '',
                'position' => '0',
                'isMain' => true,
            ];
        }
        return $imagesItems;
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @return array
     */
    public function getVideoData($product)
    {
        $mediaGalleryData = [];
        foreach ($product->getMediaGalleryImages() as $mediaGalleryImage) {
            if ($mediaGalleryImage->getMediaType() === 'external-video') {
                $mediaType = 'video';
            } else {
                $mediaType = $mediaGalleryImage->getMediaType();
            }
            $mediaGalleryData[] = [
                'mediaType' => $mediaType,
                'videoUrl' => $mediaGalleryImage->getVideoUrl(),
                'isBase' => $product->getImage() == $mediaGalleryImage->getFile(),
            ];
        }
        return $mediaGalleryData;
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @return array
     */
    public function getAdditionalInfo($product)
    {
        $result = [];
        foreach ($product->getAttributes() as $attrkey => $value) {
            if ($value->getData('is_visible_on_front')) {
                $valueData = $value->getFrontend()->getValue($product);
                if ($valueData != false && $valueData != 'No' && $valueData != 'N/A') {
                    $result[$attrkey]['value'] = $valueData;
                    $result[$attrkey]['label'] = $value->getStoreLabel();
                }
            }
        }
        return $result;
    }

    /**
     * Get config if swatch tooltips should be rendered.
     *
     * @return string
     */
    public function getShowSwatchTooltip()
    {
        return $this->scopeConfig->getValue(
            'catalog/frontend/show_swatch_tooltip',
            ScopeInterface::SCOPE_STORE
        );
    }
}
