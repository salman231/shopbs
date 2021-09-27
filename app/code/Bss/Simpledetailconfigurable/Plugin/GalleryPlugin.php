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

namespace Bss\Simpledetailconfigurable\Plugin;

use Magento\Catalog\Model\Product;

class GalleryPlugin
{
    /**
     * @var \Bss\Simpledetailconfigurable\Helper\UrlIdentifier
     */
    protected $urlIdentifier;

    /**
     * @var \Bss\Simpledetailconfigurable\Helper\ProductData
     */
    protected $productData;

    /**
     * @var \Bss\Simpledetailconfigurable\Helper\ModuleConfig
     */
    protected $moduleConfig;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $requestHttp;

    /**
     * EntityTypeArrayPlugin constructor.
     * @param \Magento\Framework\App\Request\Http $request
     */
    public function __construct(
        \Bss\Simpledetailconfigurable\Helper\UrlIdentifier $urlIdentifier,
        \Bss\Simpledetailconfigurable\Helper\ProductData $productData,
        \Bss\Simpledetailconfigurable\Helper\ModuleConfig $moduleConfig,
        \Magento\Framework\App\Request\Http $requestHttp
    ) {
        $this->urlIdentifier = $urlIdentifier;
        $this->moduleConfig = $moduleConfig;
        $this->productData = $productData;
        $this->requestHttp = $requestHttp;
    }

    /**
     * @param \Magento\Catalog\Block\Product\View\Gallery $subject
     * @param \Closure $proceed
     * @param null $image
     * @return bool|mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function aroundIsMainImage(
        \Magento\Catalog\Block\Product\View\Gallery $subject,
        \Closure $proceed,
        $image = null
    ) {
        $result = $proceed($image);
        $product = $subject->getProduct();
        $request = $this->requestHttp;
        if ($this->validateObserver($request, $product)) {
            try {
                if (!$this->productData->isAjaxLoad($product->getId())) {
                    $pathInfo = $request->getOriginalPathInfo();
                    $product->setSdcpData($pathInfo);
                    $child = $this->urlIdentifier->getChildProduct($this->removeFirstSlashes($pathInfo));
                    if ($child) {
                        return $child->getImage() == $image->getFile();
                    }
                }
            } catch (\Exception $e) {
                throw new \LogicException($e->getMessage());
            }
        }
        return $result;
    }

    /**
     * @param mixed $request
     * @param Product $product
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function validateObserver($request, $product)
    {
        return $request->getFullActionName() === 'catalog_product_view'
            && $product->getTypeId() === 'configurable'
            && $this->moduleConfig->isModuleEnable();
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
}
