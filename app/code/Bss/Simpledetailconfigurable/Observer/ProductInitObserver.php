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

namespace Bss\Simpledetailconfigurable\Observer;

use Magento\Catalog\Model\Product;
use Magento\Framework\Event\ObserverInterface;
use Bss\Simpledetailconfigurable\Model\Config\Source\GallerySwitchStrategy;
use Magento\Framework\App\Filesystem\DirectoryList;

class ProductInitObserver implements ObserverInterface
{

    /**
     * @var \Bss\Simpledetailconfigurable\Helper\UrlIdentifier
     */
    private $urlIdentifier;

    /**
     * @var \Bss\Simpledetailconfigurable\Helper\ProductData
     */
    private $productData;

    /**
     * @var \Magento\Catalog\Model\Product\Media\Config
     */
    private $catalogProductMediaConfig;

    /**
     * @var \Magento\Framework\Filesystem
     */
    private $filesystem;

    /**
     * @var \Magento\Framework\DataObjectFactory
     */
    protected $dataObjectFactory;

    /**
     * @var \Magento\Framework\Data\CollectionFactory
     */
    private $collectionFactory;

    /**
     * ProductInitObserver constructor.
     * @param \Bss\Simpledetailconfigurable\Helper\UrlIdentifier $urlIdentifier
     * @param \Bss\Simpledetailconfigurable\Helper\ProductData $productData
     * @param Product\Media\Config $catalogProductMediaConfig
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Framework\Data\CollectionFactory $collectionFactory
     * @param \Magento\Framework\DataObjectFactory $dataObjectFactory
     */
    public function __construct(
        \Bss\Simpledetailconfigurable\Helper\UrlIdentifier $urlIdentifier,
        \Bss\Simpledetailconfigurable\Helper\ProductData $productData,
        \Bss\Simpledetailconfigurable\Helper\ModuleConfig $moduleconfig,
        \Magento\Catalog\Model\Product\Media\Config $catalogProductMediaConfig,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Data\CollectionFactory $collectionFactory,
        \Magento\Framework\DataObjectFactory $dataObjectFactory
    ) {
        $this->urlIdentifier = $urlIdentifier;
        $this->productData = $productData;
        $this->moduleconfig = $moduleconfig;
        $this->catalogProductMediaConfig = $catalogProductMediaConfig;
        $this->filesystem = $filesystem;
        $this->collectionFactory = $collectionFactory;
        $this->dataObjectFactory = $dataObjectFactory;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $request = $observer->getControllerAction()->getRequest();
        $product = $observer->getProduct();
        if ($this->validateObserver($request, $product)) {
            try {
                if (!$this->productData->isAjaxLoad($product->getId())) {
                    $product = $observer->getProduct();
                    $pathInfo = $request->getOriginalPathInfo();
                    $product->setSdcpData($pathInfo);
                    $product->setPreselectData($this->getSelectingData($product->getId()));
                    $child = $this->urlIdentifier->getChildProduct($this->removeFirstSlashes($pathInfo));
                    if ($child) {
                        $this->replaceData($product, $child, $pathInfo);
                        return;
                    }
                }
                $this->replaceImageByPreselect($product);
            } catch (\Exception $e) {
                $this->replaceImageByPreselect($product);
            }
        }
    }

    /**
     * @param int $productId
     * @return array
     */
    protected function getSelectingData($productId)
    {
        return $this->productData->getPreselectKey()->create()->getCollection()->getArrayData($productId);
    }

    /**
     * @param string $pathInfo
     * @return string
     */
    protected function removeFirstSlashes($pathInfo)
    {
        $firstChar = (string)substr($pathInfo, 0, 1);
        if ($firstChar == '/') {
            $pathInfo = ltrim($pathInfo, '/');
        }

        return $pathInfo;
    }

    /**
     * @param mixed $request
     * @param Product $product
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function validateObserver($request, $product)
    {
        return $request->getFullActionName() === 'catalog_product_view'
            && $product->getTypeId() === 'configurable'
            && $this->productData->getModuleConfig()->isModuleEnable();
    }

    /**
     * @param Product $product
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function replaceImageByPreselect($product)
    {
        $preselect = $this->productData->getSelectingData($product->getId());
        if ($preselect && $this->productData->getModuleConfig()->preselectConfig() == 1) {
            $child = $product->getTypeInstance()->getProductByAttributes($preselect, $product);
            if ($child) {
                $config = $this->productData->getModuleConfig()->isShowImage();
                $this->replaceInitImage($product, $child, $config);
            }
        }
    }

    /**
     * @param Product $product
     * @param Product $child
     * @param string $pathInfo
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function replaceData($product, $child, $pathInfo)
    {
        $moduleConfig = $this->productData->getModuleConfig()->getAllConfig();
        $product->setSdcpPriceInfo($child->getPriceInfo());
        $product->setSdcpId($pathInfo);
        if ($moduleConfig['sku']) {
            $product->setSku($child->getSku());
        }
        if ($moduleConfig['name']) {
            $product->setName($child->getName());
        }
        if ($moduleConfig['meta_data']) {
            if ($child->hasMetaTitle()) {
                $product->setMetaTitle($child->getMetaTitle());
            }
            if ($child->hasMetaKeyword()) {
                $product->setMetaKeyword($child->getMetaKeyword());
            }
            if ($child->hasMetaDescription()) {
                $product->setMetaDescription($child->getMetaDescription());
            }
        }
        $this->replaceInitImage($product, $child, $moduleConfig['images']);
    }

    /**
     * @param Product $product
     * @param Product $child
     * @param string $config
     * @throws \Exception
     */
    private function replaceInitImage($product, $child, $config)
    {
        $configEnableWS = $this->isEnableConfigurableProductWholeSale($product);
        $replaceMedia = null;
        if ($config == GallerySwitchStrategy::CONFIG_REPLACE) {
            if (!$configEnableWS) {
                $product->setMediaGalleryImages($child->getMediaGalleryImages());
            }
        } elseif ($config == GallerySwitchStrategy::CONFIG_PREPEND) {
            $replaceMedia = $product->getMediaGallery('images');
            if ($childMainImage = $this->productData->getMainImage($child)) {
                $images = $this->collectionFactory->create();
                $images = $this->createGallyeryCollection($images, $childMainImage);
                $images = $this->createGallyeryCollection($images, $replaceMedia);
                $product->setMediaGalleryImages($images);
            }
        }
    }

    /**
     * @param $product
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function isEnableConfigurableProductWholeSale($product)
    {
        return $this->moduleconfig->getValueofConfig('configurableproductwholesale/general/active')
                && ($product->getEnableCpwd() || $product->getEnableCpwd() == null);
    }

    /**
     * @param Image $images
     * @param Collection $listImage
     * @return mixed
     */
    private function createGallyeryCollection($images, $listImage)
    {
        $directory = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA);
        foreach ($listImage as $image) {
            if (!empty($image['disabled'])
                || !empty($image['removed'])
                || empty($image['value_id'])
                || $images->getItemById($image['value_id']) != null
            ) {
                continue;
            }
            $image['url'] = $this->catalogProductMediaConfig->getMediaUrl($image['file']);
            $image['id'] = $image['value_id'];
            $image['path'] = $directory->getAbsolutePath(
                $this->catalogProductMediaConfig->getMediaPath($image['file'])
            );
            $images->addItem($this->dataObjectFactory->create()->addData($image));
        }
        return $images;
    }
}
