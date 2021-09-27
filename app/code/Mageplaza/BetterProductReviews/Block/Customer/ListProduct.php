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

namespace Mageplaza\BetterProductReviews\Block\Customer;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Helper\ImageFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template\Context;
use Magento\Review\Model\ResourceModel\Review\CollectionFactory as ReviewsColFactory;
use Magento\Review\Model\ResourceModel\Review\Product\Collection;
use Magento\Review\Model\ResourceModel\Review\Product\CollectionFactory as ReviewsProdColFact;
use Mageplaza\BetterProductReviews\Block\Review;
use Mageplaza\BetterProductReviews\Helper\Data as HelperData;
use Mageplaza\BetterProductReviews\Helper\Image as HelperImage;

/**
 * Class ListProduct
 *
 * @package Mageplaza\BetterProductReviews\Block\Customer
 */
class ListProduct extends Review
{
    /**
     * Product reviews collection
     *
     * @var Collection
     */
    protected $_reviewsProductCol;

    /**
     * @var ImageFactory
     */
    protected $_imageHelperFactory;

    /**
     * @var ReviewsProdColFact
     */
    protected $_reviewsProdColFact;

    /**
     * ListProduct constructor.
     *
     * @param Context $context
     * @param Registry $coreRegistry
     * @param ImageFactory $imageHelperFactory
     * @param ReviewsColFactory $reviewsColFactory
     * @param ProductRepositoryInterface $productRepository
     * @param ReviewsProdColFact $reviewsProdColFact
     * @param HelperData $helperData
     * @param HelperImage $helperImage
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        ImageFactory $imageHelperFactory,
        ReviewsColFactory $reviewsColFactory,
        ProductRepositoryInterface $productRepository,
        ReviewsProdColFact $reviewsProdColFact,
        HelperData $helperData,
        HelperImage $helperImage,
        array $data = []
    ) {
        $this->_imageHelperFactory = $imageHelperFactory;
        $this->_reviewsProdColFact = $reviewsProdColFact;

        parent::__construct(
            $context,
            $coreRegistry,
            $reviewsColFactory,
            $productRepository,
            $helperData,
            $helperImage,
            $data
        );
    }

    /**
     * @return array
     * @throws NoSuchEntityException
     */
    public function getPurchasedProductIds()
    {
        return $this->_helperData->getPurchasedProductIds();
    }

    /**
     * @param string $productId
     *
     * @return string
     * @throws NoSuchEntityException
     */
    public function getProductImgUrl($productId)
    {
        $product = $this->getProductById($productId);
        $imageUrl = $this->_imageHelperFactory->create()
            ->init($product, 'product_page_image_large')->setImageFile($product->getImage())->getUrl();

        return str_replace('\\', '/', $imageUrl);
    }

    /**
     * @param string $productId
     *
     * @return string
     * @throws NoSuchEntityException
     */
    public function getProductName($productId)
    {
        return $this->getProductById($productId)->getName();
    }

    /**
     * @return array
     * @throws NoSuchEntityException
     */
    public function getReviewedProductIds()
    {
        if (!($customerId = $this->_helperData->getCustomerId())) {
            return [];
        }
        if (!$this->_reviewsProductCol) {
            $this->_reviewsProductCol = $this->_reviewsProdColFact->create();
            $this->_reviewsProductCol
                ->addStoreFilter($this->_storeManager->getStore()->getId())
                ->addCustomerFilter($customerId)
                ->setDateOrder();
        }
        $productIds = [];
        if ($this->_reviewsProductCol && !empty($this->_reviewsProductCol)) {
            foreach ($this->_reviewsProductCol as $review) {
                $productIds[] = $review->getEntityId();
            }
        }

        return $productIds;
    }

    /**
     * @return string
     */
    public function isReviewRemindEnable()
    {
        return $this->_helperData->getConfigGeneral('review_remind');
    }

    /**
     * @param $productId
     * @return bool|string
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getWriteReviewRestrictionCustomerGroup($productId)
    {
        $isEnabled = $this->isWriteReviewCustomerGroupEnabled($productId);
        if ($isEnabled) {
            $customerGroupConfig = $this->_helperData->getWriteReviewConfig('customer_group');
            $customerGroups = explode(',', $customerGroupConfig);

            if (!in_array($this->_helperData->getCustomerGroupId(), $customerGroups, true)) {
                $isEnabled = false;
            }
        }

        return $isEnabled;
    }

    /**
     * Get write review enabled config
     *
     * @param $productId
     * @return bool
     */
    public function isWriteReviewCustomerGroupEnabled($productId)
    {
        $config = $this->_helperData->getWriteReviewConfig('enabled');
        $isEnabled = $this->_helperData->isPurchaserConfig($config, $productId);

        return $isEnabled;
    }
}
