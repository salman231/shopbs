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

namespace Mageplaza\BetterProductReviews\Helper;

use Magento\Bundle\Model\Product\Type as BundleProduct;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as ConfigurableProduct;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\ObjectManagerInterface;
use Magento\GroupedProduct\Model\Product\Type\Grouped as GroupedProduct;
use Magento\Review\Model\RatingFactory;
use Magento\Review\Model\ResourceModel\Review\Collection;
use Magento\Review\Model\ResourceModel\Review\CollectionFactory as ReviewsColFactory;
use Magento\Review\Model\Review as ReviewModel;
use Magento\Review\Model\ReviewFactory;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Item;
use Magento\Sales\Model\OrderFactory;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\BetterProductReviews\Model\Config\Source\System\CustomerRestriction;
use Mageplaza\BetterProductReviews\Model\Customer\Context as CustomerSessionContext;
use Mageplaza\Core\Helper\AbstractData;

/**
 * Class Data
 *
 * @package Mageplaza\TwoFactorAuth\Helper
 */
class Data extends AbstractData
{
    const CONFIG_MODULE_PATH = 'mpbetterproductreviews';
    const XML_PATH_WRITE_REVIEW = 'write_review';
    const XML_PATH_REVIEW_LISTING = 'review_listing';

    /**
     * @var CustomerSession
     */
    protected $_customerSession;

    /**
     * @var OrderFactory
     */
    protected $_orderFactory;

    /**
     * @var HttpContext
     */
    protected $_httpContext;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $_customerRepository;

    /**
     * @var Configurable
     */
    protected $_configureProduct;

    /**
     * @var BundleProduct
     */
    protected $_bundleProduct;

    /**
     * @var GroupedProduct
     */
    protected $_groupedProduct;

    /**
     * @var RatingFactory
     */
    protected $_ratingFactory;

    /**
     * @var ReviewFactory
     */
    protected $_reviewFactory;

    /**
     * @var ReviewsColFactory
     */
    protected $_reviewsColFactory;

    /**
     * @var ProductRepositoryInterface
     */
    protected $_productRepository;

    /**
     * Data constructor.
     *
     * @param Context $context
     * @param ObjectManagerInterface $objectManager
     * @param StoreManagerInterface $storeManager
     * @param CustomerSession $customerSession
     * @param CustomerRepositoryInterface $customerRepository
     * @param OrderFactory $orderFactory
     * @param HttpContext $httpContext
     * @param BundleProduct $bundleProduct
     * @param Configurable $configurable
     * @param GroupedProduct $groupedProduct
     * @param RatingFactory $ratingFactory
     * @param ReviewFactory $reviewFactory
     * @param ReviewsColFactory $reviewsColFactory
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(
        Context $context,
        ObjectManagerInterface $objectManager,
        StoreManagerInterface $storeManager,
        CustomerSession $customerSession,
        CustomerRepositoryInterface $customerRepository,
        OrderFactory $orderFactory,
        HttpContext $httpContext,
        BundleProduct $bundleProduct,
        Configurable $configurable,
        GroupedProduct $groupedProduct,
        RatingFactory $ratingFactory,
        ReviewFactory $reviewFactory,
        ReviewsColFactory $reviewsColFactory,
        ProductRepositoryInterface $productRepository
    ) {
        $this->_customerSession = $customerSession;
        $this->_customerRepository = $customerRepository;
        $this->_orderFactory = $orderFactory;
        $this->_httpContext = $httpContext;
        $this->_groupedProduct = $groupedProduct;
        $this->_bundleProduct = $bundleProduct;
        $this->_configureProduct = $configurable;
        $this->_ratingFactory = $ratingFactory;
        $this->_reviewFactory = $reviewFactory;
        $this->_reviewsColFactory = $reviewsColFactory;
        $this->_productRepository = $productRepository;

        parent::__construct($context, $objectManager, $storeManager);
    }

    /**
     * @return CustomerSession
     */
    public function getCustomerSession()
    {
        return $this->_customerSession;
    }

    /**
     * @param string $code
     * @param null $storeId
     *
     * @return string
     */
    public function getWriteReviewConfig($code = '', $storeId = null)
    {
        $code = ($code !== '') ? '/' . $code : '';

        return $this->getModuleConfig(self::XML_PATH_WRITE_REVIEW . $code, $storeId);
    }

    /**
     * @param string $code
     * @param null $storeId
     *
     * @return string
     */
    public function getReviewListingConfig($code = '', $storeId = null)
    {
        $code = ($code !== '') ? '/' . $code : '';

        return $this->getModuleConfig(self::XML_PATH_REVIEW_LISTING . $code, $storeId);
    }

    /**
     * @return string
     */
    public function isAjaxReviewSubmit()
    {
        return $this->getWriteReviewConfig('use_ajax');
    }

    /**
     * @return int|mixed
     */
    public function getCustomerId()
    {
        $customerId = 0;
        if ($this->isLoggedIn()) {
            $customerId = $this->_httpContext->getValue(CustomerSessionContext::CONTEXT_CUSTOMER_ID);
        }

        return $customerId;
    }

    /**
     * @return int|null|string
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getCustomerGroupId()
    {
        $customerGroup = '0';
        if ($this->isLoggedIn()) {
            $customer = $this->_customerRepository->getById($this->getCustomerId());
            $customerGroup = $customer->getGroupId();
        }

        return $customerGroup;
    }

    /**
     * @param string $productId
     *
     * @return bool
     */
    public function isPurchaser($productId)
    {
        $result = false;
        $currentCustomerId = $this->getCustomerId();

        if ($currentCustomerId) {
            $orders = $this->_orderFactory->create()->getCollection()
                ->addFieldToFilter('customer_id', $currentCustomerId)
                ->addFieldToFilter('state', Order::STATE_COMPLETE);
            foreach ($orders as $order) {
                /**
                 * @var Order $order
                 */
                foreach ($order->getAllVisibleItems() as $item) {
                    /** @var Item $item */
                    if ($productId == $item->getProductId()) {
                        $result = true;
                        break;
                    }
                }
            }
        }

        return $result;
    }

    /**
     * @return array
     * @throws NoSuchEntityException
     */
    public function getPurchasedProductIds()
    {
        $productList = [];
        if ($customerId = $this->getCustomerId()) {
            $orders = $this->_orderFactory->create()->getCollection()
                ->addFieldToFilter('customer_id', $customerId)
                ->addFieldToFilter('state', Order::STATE_COMPLETE)
                ->addFieldToFilter('store_id', $this->storeManager->getStore()->getId())
                ->setOrder('main_table.created_at', 'desc');

            foreach ($orders as $order) {
                /**
                 * @var Order $order
                 */
                foreach ($order->getAllVisibleItems() as $item) {
                    /** @var Item $item */
                    $productList[] = $this->getParentProductId($item->getProductId());
                }
            }
        }
        $productList = array_unique($productList);

        return $productList;
    }

    /**
     * @param string $childId
     *
     * @return string
     * @throws NoSuchEntityException
     */
    public function getParentProductId($childId)
    {
        $product = $this->_productRepository->getById($childId);

        switch ($product->getTypeId()) {
            case ConfigurableProduct::TYPE_CODE:
                $parentIds = $this->_configureProduct->getParentIdsByChild($childId);
                if (isset($parentIds[0])) {
                    $parentId = $parentIds[0];

                    return $parentId;
                }
                break;

            case BundleProduct::TYPE_CODE:
                $parentIds = $this->_bundleProduct->getParentIdsByChild($childId);
                if (isset($parentIds[0])) {
                    $parentId = $parentIds[0];

                    return $parentId;
                }
                break;

            case GroupedProduct::TYPE_CODE:
                $parentIds = $this->_groupedProduct->getParentIdsByChild($childId);
                if (isset($parentIds[0])) {
                    $parentId = $parentIds[0];

                    return $parentId;
                }
                break;

            default:
                $parentIds = $this->_groupedProduct->getParentIdsByChild($childId);
                if (isset($parentIds[0])) {
                    $parentId = $parentIds[0];

                    return $parentId;
                }
        }

        return $childId;
    }

    /**
     * @param string|int $config
     * @param string $productId
     *
     * @return bool
     */
    public function isPurchaserConfig($config, $productId)
    {
        if ($config != CustomerRestriction::PURCHASERS_ONLY) {
            $isEnabled = (bool)$config;
        } else {
            $isEnabled = $this->isPurchaser($productId);
        }

        return $isEnabled;
    }

    /**
     * Check customer is logged in or not
     *
     * @return bool
     */
    public function isLoggedIn()
    {
        return $this->_request->isAjax() ? $this->getCustomerSession()->isLoggedIn()
            : (bool)$this->_httpContext->getValue(\Magento\Customer\Model\Context::CONTEXT_AUTH);
    }

    /**
     * @param $data
     * @param string $prefixComment
     * @param string $subFixComment
     *
     * @return string
     */
    public function createStructuredData($data, $prefixComment = '', $subFixComment = '')
    {
        $applicationLdJson = $prefixComment;
        $applicationLdJson .= '<script type="application/ld+json">' . self::jsonEncode($data) . '</script>';
        $applicationLdJson .= $subFixComment;

        return $applicationLdJson;
    }

    /**
     * @param string $moduleName
     *
     * @return bool
     */
    public function isModuleEnabled($moduleName)
    {
        return $this->_moduleManager->isEnabled($moduleName);
    }

    /**
     * @param Product $product
     *
     * @return int
     * @throws NoSuchEntityException
     */
    public function getReviewCount($product)
    {
        $this->_reviewFactory->create()->getEntitySummary($product, $this->storeManager->getStore()->getId());
        $reviewCount = $product->getRatingSummary()->getReviewsCount();
        $product->setRatingSummary(null);

        return $reviewCount;
    }

    /**
     * @param Product $product
     *
     * @return array
     * @throws NoSuchEntityException
     */
    public function getRatingSummary($product)
    {
        $this->_reviewFactory->create()->getEntitySummary($product, $this->storeManager->getStore()->getId());
        $ratingSummary = $product->getRatingSummary()->getRatingSummary();
        $product->setRatingSummary(null);

        return $ratingSummary;
    }

    /**
     * @param string $reviewId
     *
     * @return array
     */
    public function getReviewRatingSummary($reviewId)
    {
        return $this->_ratingFactory->create()->getReviewSummary($reviewId);
    }

    /**
     * @param ReviewModel $review
     *
     * @return string
     */
    public function getReviewRatingValue($review)
    {
        $ratingPercent = 0;
        if ($reviewCount = $this->getReviewRatingSummary($review->getId())->getCount()) {
            $ratingPercent = ceil($this->getReviewRatingSummary($review->getId())->getSum() / $reviewCount);
        } elseif (!empty($review->getRatingVotes())) {
            foreach ($review->getRatingVotes() as $vote) {
                $ratingPercent = $vote;
            }
        }
        $ratingValue = number_format((float)($ratingPercent / 20), 1);

        return $ratingValue;
    }

    /**
     * @param Product $product
     *
     * @return Collection
     * @throws NoSuchEntityException
     */
    public function getReviewsCollection($product)
    {
        /**
         * @var Collection $reviewsCollection
         */
        $reviewsCollection = $this->_reviewsColFactory->create()->addReviewDetail()
            ->addStoreFilter(
                $this->storeManager->getStore()->getId()
            )->addStatusFilter(
                ReviewModel::STATUS_APPROVED
            )->addEntityFilter(
                'product',
                $product->getId()
            )->addRateVotes()->setDateOrder();

        return $reviewsCollection;
    }
}
