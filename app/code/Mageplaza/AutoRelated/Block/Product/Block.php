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
 * @category    Mageplaza
 * @package     Mageplaza_AutoRelated
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\AutoRelated\Block\Product;

use Magento\Catalog\Block\Product\AbstractProduct;
use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Url\Helper\Data as UrlData;
use Magento\Framework\View\LayoutFactory;
use Magento\Widget\Block\BlockInterface;
use Magento\Wishlist\Controller\WishlistProviderInterface;
use Mageplaza\AutoRelated\Helper\Data;
use Mageplaza\AutoRelated\Helper\Rule;
use Mageplaza\AutoRelated\Model\Config\Source\AddProductTypes;
use Mageplaza\AutoRelated\Model\Config\Source\Direction;
use Mageplaza\AutoRelated\Model\Config\Source\ProductNotDisplayed;

/**
 * Class ProductList
 * @package Mageplaza\AutoRelated\Block\Product\ProductList
 */
class Block extends AbstractProduct implements BlockInterface
{
    /**
     * @var string
     */
    protected $_template = 'Mageplaza_AutoRelated::product/block.phtml';

    /**
     * @var CollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * @var \Mageplaza\AutoRelated\Model\Rule
     */
    protected $rule;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var array
     */
    protected $displayTypes;

    /**
     * @var Session
     */
    protected $checkoutSession;

    /**
     * @var WishlistProviderInterface
     */
    protected $wishListProvider;

    /**
     * @var UrlData
     */
    protected $urlHelper;

    /**
     * @var
     */
    protected $rendererListBlock;

    /**
     * @var LayoutFactory
     */
    private $layoutFactory;

    /**
     * Block constructor.
     *
     * @param Context $context
     * @param CollectionFactory $productCollectionFactory
     * @param Session $checkoutSession
     * @param WishlistProviderInterface $wishListProvider
     * @param Rule $helper
     * @param UrlData $urlHelper
     * @param LayoutFactory|null $layoutFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        CollectionFactory $productCollectionFactory,
        Session $checkoutSession,
        WishlistProviderInterface $wishListProvider,
        Rule $helper,
        UrlData $urlHelper,
        LayoutFactory $layoutFactory = null,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->productCollectionFactory = $productCollectionFactory;
        $this->checkoutSession          = $checkoutSession;
        $this->wishListProvider         = $wishListProvider;
        $this->helper                   = $helper;
        $this->urlHelper                = $urlHelper;
        $this->layoutFactory            = $layoutFactory ?: ObjectManager::getInstance()->get(LayoutFactory::class);
    }

    /**
     * @param \Mageplaza\AutoRelated\Model\Rule $rule
     *
     * @return $this
     */
    public function setRule($rule)
    {
        $this->rule = $rule;

        $location = $rule->getLocation();
        if ($location === 'left-popup-content' || $location === 'right-popup-content') {
            $this->setTemplate('Mageplaza_AutoRelated::product/block-floating.phtml');
        }

        return $this;
    }

    /**
     * @return mixed|string
     */
    public function getLocationBlock()
    {
        return $this->rule->getLocation();
    }

    /**
     * Get heading label
     *
     * @return string
     */
    public function getTitleBlock()
    {
        return $this->rule->getBlockName();
    }

    /**
     * @return mixed
     */
    public function getRuleId()
    {
        return $this->rule->getId();
    }

    /**
     * @return string
     */
    public function getJsData()
    {
        return Rule::jsonEncode([
            'type'      => $this->isSliderType() ? 'slider' : 'grid',
            'rule_id'   => $this->rule->getId(),
            'parent_id' => $this->rule->getData('parent_id'),
            'location'  => $this->rule->getData('location'),
            'mode'      => $this->rule->getData('display_mode')
        ]);
    }

    /**
     * Get layout config
     *
     * @return int
     */
    public function isSliderType()
    {
        return !$this->rule->getProductLayout();
    }

    /**
     * @param $type
     *
     * @return bool
     */
    public function canShow($type)
    {
        if ($this->displayTypes === null) {
            $this->displayTypes = $this->rule->getDisplayAdditional() ? explode(
                ',',
                $this->rule->getDisplayAdditional()
            ) : [];
        }

        return in_array($type, $this->displayTypes);
    }

    /**
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getProductCollection()
    {
        $rule = $this->rule;
        if (!$rule || !$rule->getId()) {
            return [];
        }

        $productIds = $rule->getApplyProductIds();
        if (empty($productIds)) {
            return [];
        }
        if ($rule->getAddRucProduct()) {
            $productIds = array_unique(array_merge($productIds, $this->addAdditionProducts()));
        }
        if ($this->rule->getProductNotDisplayed()) {
            $productIds = array_diff($productIds, $this->removeProducts());
        }
        if (empty($productIds)) {
            return [];
        }

        /** @var Collection $collection */
        $collection = $this->productCollectionFactory->create()
            ->addIdFilter($productIds)
            ->addStoreFilter();
        $this->_addProductAttributesAndPrices($collection);

        if ($rule->getDisplayOutOfStock()) {
            $collection->setFlag('has_stock_status_filter', true);
        }
        if ($limit = $rule->getLimitNumber()) {
            $collection->setPageSize($limit);
        }

        switch ($rule->getSortOrderDirection()) {
            case Direction::BESTSELLER:
                $collection->getSelect()->joinLeft(
                    ['soi' => $collection->getTable('sales_bestsellers_aggregated_yearly')],
                    'e.entity_id = soi.product_id',
                    ['qty_ordered' => 'SUM(soi.qty_ordered)']
                )
                    ->group('e.entity_id')
                    ->order('qty_ordered DESC');
                break;
            case Direction::PRICE_LOW:
                $collection->addAttributeToSort('price', 'ASC');
                break;
            case Direction::PRICE_HIGH:
                $collection->addAttributeToSort('price', 'DESC');
                break;
            case Direction::NEWEST:
                $collection->getSelect()->order('e.created_at DESC');
                break;
            default:
                $collection->getSelect()->order('rand()');
                break;
        }

        return $collection;
    }

    /**
     * @return array|string
     */
    protected function addAdditionProducts()
    {
        $productIds = [];
        if ($this->rule->getBlockType() !== 'product') {
            return $productIds;
        }

        $product = $this->helper->getCurrentProduct();

        $addProductTypes = explode(',', $this->rule['add_ruc_product']);
        if (in_array(AddProductTypes::RELATED_PRODUCT, $addProductTypes)) {
            $productIds += $product->getRelatedProductIds();
        }
        if (in_array(AddProductTypes::UP_SELL_PRODUCT, $addProductTypes)) {
            $productIds += $product->getUpSellProductIds();
        }
        if (in_array(AddProductTypes::CROSS_SELL_PRODUCT, $addProductTypes)) {
            $productIds += $product->getCrossSellProductIds();
        }

        return $productIds;
    }

    /**
     * @return array
     */
    protected function removeProducts()
    {
        $productIds = [];

        $productNotDisplayed = explode(',', $this->rule['product_not_displayed']);
        if (in_array(ProductNotDisplayed::IN_CART, $productNotDisplayed)) {
            $productInfo = $this->checkoutSession->getQuote()->getItemsCollection();
            foreach ($productInfo as $item) {
                $productIds[] = $item->getProductId();
            }
        }
        if (in_array(
            ProductNotDisplayed::IN_WISHLIST,
            $productNotDisplayed
        ) && ($currentUserWishList = $this->wishListProvider->getWishlist())) {
            $wishListItems = $currentUserWishList->getItemCollection();
            foreach ($wishListItems as $item) {
                $productIds[] = $item->getProductId();
            }
        }

        return $productIds;
    }

    /**
     * @inheritdoc
     */
    protected function getDetailsRendererList()
    {
        if (empty($this->rendererListBlock)) {
            $layout = $this->layoutFactory->create(['cacheable' => false]);
            $layout->getUpdate()->addHandle('catalog_widget_product_list')->load();
            $layout->generateXml();
            $layout->generateElements();

            $this->rendererListBlock = $layout->getBlock('category.product.type.widget.details.renderers');
        }

        return $this->rendererListBlock;
    }

    /**
     * Get post parameters
     *
     * @param Product $product
     *
     * @return array
     */
    public function getAddToCartPostParams(Product $product)
    {
        $url = $this->getAddToCartUrl($product, ['_escape' => false]);

        return [
            'action' => $url,
            'data'   => [
                'product'                               => (int) $product->getEntityId(),
                ActionInterface::PARAM_NAME_URL_ENCODED => $this->urlHelper->getEncodedUrl($url),
            ]
        ];
    }
}
