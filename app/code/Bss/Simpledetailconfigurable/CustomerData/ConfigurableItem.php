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
namespace Bss\Simpledetailconfigurable\CustomerData;

use Magento\Catalog\Model\Config\Source\Product\Thumbnail as ThumbnailSource;
use Magento\Checkout\CustomerData\DefaultItem;
use Magento\Quote\Model\Quote\Item;

class ConfigurableItem extends DefaultItem
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Bss\Simpledetailconfigurable\Helper\ModuleConfig
     */
    protected $helper;

    /**
     * ConfigurableItem constructor.
     * @param \Magento\Catalog\Helper\Image $imageHelper
     * @param \Magento\Msrp\Helper\Data $msrpHelper
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param \Magento\Catalog\Helper\Product\ConfigurationPool $configurationPool
     * @param \Magento\Checkout\Helper\Data $checkoutHelper
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Bss\Simpledetailconfigurable\Helper\ModuleConfig $helper
     */
    public function __construct(
        \Magento\Catalog\Helper\Image $imageHelper,
        \Magento\Msrp\Helper\Data $msrpHelper,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Catalog\Helper\Product\ConfigurationPool $configurationPool,
        \Magento\Checkout\Helper\Data $checkoutHelper,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Bss\Simpledetailconfigurable\Helper\ModuleConfig $helper
    ) {
        parent::__construct(
            $imageHelper,
            $msrpHelper,
            $urlBuilder,
            $configurationPool,
            $checkoutHelper
        );
        $this->scopeConfig = $scopeConfig;
        $this->helper = $helper;
    }

    /**
     * @param Item $item
     * @return array
     */
    public function getItemData(Item $item)
    {
        $result = parent::getItemData($item);
        if ($this->helper->isShowName() && $child = $this->getChildProduct()) {
            $result['product_name'] = $child->getName();
        }
        return $result;
    }

    /**
     * @return \Magento\Catalog\Model\Product
     */
    protected function getProductForThumbnail()
    {
        if (version_compare($this->helper->getMagentoVersion(), '2.3.0', '<')) {
            $config = $this->scopeConfig->getValue(
                \Magento\ConfigurableProduct\Block\Cart\Item\Renderer\Configurable::CONFIG_THUMBNAIL_SOURCE,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
            $thumbnail = $this->getChildProduct()->getThumbnail();
            if ($config == ThumbnailSource::OPTION_USE_PARENT_IMAGE || (!$thumbnail || $thumbnail == 'no_selection')) {
                return $this->getProduct();
            }
            return $this->getChildProduct();
        } else {
            return parent::getProductForThumbnail();
        }
    }

    /**
     * Get item configurable child product
     *
     * @return \Magento\Catalog\Model\Product
     */
    protected function getChildProduct()
    {
        if ($option = $this->item->getOptionByCode('simple_product')) {
            return $option->getProduct();
        }
        return $this->getProduct();
    }
}
