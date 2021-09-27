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
 * @package   Mageplaza_BetterProductReviews
 * @copyright Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license   https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\BetterProductReviews\Block;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Review\Model\ResourceModel\Review\CollectionFactory as ReviewsColFactory;
use Mageplaza\BetterProductReviews\Helper\Data as HelperData;
use Mageplaza\BetterProductReviews\Helper\Image as HelperImage;

/**
 * Class Review
 *
 * @package Mageplaza\BetterProductReviews\Block
 */
class Review extends Template
{
    /**
     * @var Registry
     */
    protected $_coreRegistry;

    /**
     * @var ReviewsColFactory
     */
    protected $_reviewsColFactory;

    /**
     * @var ProductRepositoryInterface
     */
    protected $_productRepository;

    /**
     * @var HelperData
     */
    protected $_helperData;

    /**
     * @var HelperImage
     */
    protected $_helperImage;

    /**
     * Review constructor.
     *
     * @param Context $context
     * @param Registry $coreRegistry
     * @param ReviewsColFactory $reviewsColFactory
     * @param ProductRepositoryInterface $productRepository
     * @param HelperData $helperData
     * @param HelperImage $helperImage
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        ReviewsColFactory $reviewsColFactory,
        ProductRepositoryInterface $productRepository,
        HelperData $helperData,
        HelperImage $helperImage,
        array $data = []
    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->_reviewsColFactory = $reviewsColFactory;
        $this->_productRepository = $productRepository;
        $this->_helperData = $helperData;
        $this->_helperImage = $helperImage;

        parent::__construct($context, $data);
    }

    /**
     * @return Product
     * @throws NoSuchEntityException
     */
    public function getProduct()
    {
        if (!$this->_coreRegistry->registry('product') && $this->getProductId()) {
            $product = $this->_productRepository->getById($this->getProductId());
            $this->_coreRegistry->register('product', $product);
        }

        return $this->_coreRegistry->registry('product');
    }

    /**
     * Get product id
     *
     * @return int|null
     */
    public function getProductId()
    {
        $product = $this->_coreRegistry->registry('product');

        return $product ? $product->getId() : null;
    }

    /**
     * Get current store id
     *
     * @return int
     * @throws NoSuchEntityException
     */
    public function getCurrentStoreId()
    {
        return $this->_storeManager->getStore()->getId();
    }

    /**
     * @param null $productId
     * @param bool $useDirectLink
     *
     * @return string
     * @throws NoSuchEntityException
     */
    public function getReviewsUrl($productId = null, $useDirectLink = false)
    {
        if (!$productId) {
            $productId = ($this->getProductId()) ?: $this->getAjaxProductId();
        }
        /**
         * @var Product $product
         */
        $product = $this->_productRepository->getById($productId);

        if ($useDirectLink) {
            return $this->getUrl(
                'review/product/list',
                ['id' => $productId, 'category' => $product->getCategoryId()]
            );
        }

        return $product->getUrlModel()->getUrl($product, ['_ignore_category' => true]);
    }

    /**
     * Get recommend product enabled config
     *
     * @return bool
     */
    public function isRecommendProductEnabled()
    {
        $config = $this->_helperData->getWriteReviewConfig('recommend_product');
        $productId = ($this->getProductId()) ?: $this->getAjaxProductId();

        return $this->_helperData->isPurchaserConfig($config, $productId);
    }

    /**
     * Get write review enabled config
     *
     * @return bool
     */
    public function isWriteReviewEnabled()
    {
        $config = $this->_helperData->getWriteReviewConfig('enabled');
        $isEnabled = $this->_helperData->isPurchaserConfig($config, $this->getProductId());

        return $isEnabled;
    }

    /**
     * Get write review restriction config
     *
     * @return string
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getWriteReviewRestriction()
    {
        $isEnabled = $this->isWriteReviewEnabled();
        if ($isEnabled) {
            $customerGroupConfig = $this->_helperData->getWriteReviewConfig('customer_group');
            $customerGroups = explode(',', $customerGroupConfig);

            if (!in_array($this->_helperData->getCustomerGroupId(), $customerGroups, true)) {
                $isEnabled = false;
            }
        }

        return HelperData::jsonEncode($isEnabled);
    }

    /**
     * @param string $productId
     *
     * @return Product
     * @throws NoSuchEntityException
     */
    public function getProductById($productId)
    {
        /**
         * @var Product $product
         */
        $product = $this->_productRepository->getById($productId);

        return $product;
    }

    /**
     * Get customer write review restriction notice message
     *
     * @return string
     */
    public function getCustomerNotice()
    {
        return ($this->_helperData->getWriteReviewConfig('notice_message'))
            ?: __('Sorry, you haven\'t got the permission to write a review.');
    }
}
