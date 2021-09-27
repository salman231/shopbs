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

namespace Mageplaza\BetterProductReviews\Observer\Model\Order;

use Exception;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Review\Model\ResourceModel\Review\Collection;
use Magento\Review\Model\ResourceModel\Review\CollectionFactory;
use Magento\Sales\Model\Order;
use Mageplaza\BetterProductReviews\Helper\Data as HelperData;
use Mageplaza\BetterProductReviews\Model\Config\Source\BuyerType;

/**
 * Class Save
 * @package Mageplaza\BetterProductReviews\Model\Order\Save
 */
class Save implements ObserverInterface
{
    /**
     * @var HelperData
     */
    protected $_helperData;

    /**
     * @var CollectionFactory
     */
    private $reviewCollection;

    /**
     * @var Configurable
     */
    private $configurable;

    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * Save constructor.
     *
     * @param HelperData $helperData
     * @param CollectionFactory $reviewCollection
     * @param ResourceConnection $resource
     * @param Configurable $configurable
     */
    public function __construct(
        HelperData $helperData,
        CollectionFactory $reviewCollection,
        ResourceConnection $resource,
        Configurable $configurable
    ) {
        $this->_helperData = $helperData;
        $this->reviewCollection = $reviewCollection;
        $this->configurable = $configurable;
        $this->resource = $resource;
    }

    /**
     * @param Observer $observer
     *
     * @throws Exception
     */
    public function execute(Observer $observer)
    {
        /** @var Order $order */
        $order = $observer->getData('order');

        if ($order->getState() === Order::STATE_COMPLETE &&
            $order->getCustomerId() &&
            $this->_helperData->isEnabled()
        ) {
            foreach ($order->getItems() as $orderItem) {
                $productId = $orderItem->getProductId();
                $parent = $this->configurable->getParentIdsByChild($productId);
                if (isset($parent[0])) {
                    $productId = $parent[0];
                }
                $result = $this->getReviewResult($productId, $order->getCustomerId(), $order->getStoreId());
                foreach ($result as $reviewId) {
                    $this->updateReviewDetail($reviewId);
                }
            }
        }
    }

    /**
     * @param int $productId
     * @param int $customerId
     * @param int $storeId
     *
     * @return array
     */
    private function getReviewResult($productId, $customerId, $storeId)
    {
        $reviewIds = [];
        /** @var Collection $connection */
        $connection = $this->reviewCollection->create();
        $connection->addStoreFilter($storeId)
            ->addEntityFilter('product', $productId)
            ->addCustomerFilter($customerId);
        foreach ($connection->getItems() as $item) {
            $reviewIds[] = $item->getReviewId();
        }

        return $reviewIds;
    }

    /**
     * @param int $reviewId
     */
    private function updateReviewDetail($reviewId)
    {
        $connection = $this->resource->getConnection();
        $detailTableName = $this->resource->getTableName('review_detail');
        $data = ['mp_bpr_verified_buyer' => BuyerType::VERIFIED_BUYER];
        $where = ['review_id = ?' => $reviewId];
        $connection->update($detailTableName, $data, $where);
    }
}
