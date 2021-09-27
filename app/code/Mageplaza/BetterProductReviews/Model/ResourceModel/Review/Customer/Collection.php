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

namespace Mageplaza\BetterProductReviews\Model\ResourceModel\Review\Customer;

use Magento\Customer\Model\ResourceModel\Customer;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Data\Collection\EntityFactory;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Reports\Model\ResourceModel\Review\Customer\Collection as CustomerCollection;
use Magento\Review\Helper\Data;
use Magento\Review\Model\Rating\Option\VoteFactory;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\BetterProductReviews\Helper\Data as HelperData;
use Mageplaza\BetterProductReviews\Model\Config\Source\System\ReviewStatus;
use Psr\Log\LoggerInterface;

/**
 * Class Collection
 *
 * @package Mageplaza\BetterProductReviews\Model\ResourceModel\Review\Customer
 */
class Collection extends CustomerCollection
{
    /**
     * @var HelperData
     */
    protected $_helperData;

    /**
     * Collection constructor.
     *
     * @param EntityFactory $entityFactory
     * @param LoggerInterface $logger
     * @param FetchStrategyInterface $fetchStrategy
     * @param ManagerInterface $eventManager
     * @param Data $reviewData
     * @param VoteFactory $voteFactory
     * @param StoreManagerInterface $storeManager
     * @param Customer $customerResource
     * @param HelperData $helperData
     * @param null $connection
     * @param AbstractDb|null $resource
     */
    public function __construct(
        EntityFactory $entityFactory,
        LoggerInterface $logger,
        FetchStrategyInterface $fetchStrategy,
        ManagerInterface $eventManager,
        Data $reviewData,
        VoteFactory $voteFactory,
        StoreManagerInterface $storeManager,
        Customer $customerResource,
        HelperData $helperData,
        $connection = null,
        AbstractDb $resource = null
    ) {
        $this->_helperData = $helperData;

        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $reviewData,
            $voteFactory,
            $storeManager,
            $customerResource,
            $connection,
            $resource
        );
    }

    /**
     * @return $this|CustomerCollection
     */
    protected function _initSelect()
    {
        $configReviewStatus = (int)$this->_helperData->getConfigGeneral('review_status');

        if ($configReviewStatus !== ReviewStatus::BOTH && $this->_helperData->isEnabled()) {
            parent::_initSelect();
            $this->addFieldToFilter('status_id', $configReviewStatus);

            return $this;
        }

        return parent::_initSelect();
    }
}
