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

namespace Mageplaza\BetterProductReviews\Block\Adminhtml\Report;

use Exception;
use Magento\Backend\Block\Template;
use Magento\Framework\Phrase;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Reports\Model\ResourceModel\Review\Customer\Collection;
use Magento\Reports\Model\ResourceModel\Review\Customer\CollectionFactory;

/**
 * Class ReviewByCustomer
 *
 * @package Mageplaza\BetterProductReviews\Block\Adminhtml\Report
 */
class ReviewByCustomer extends AbstractClass
{
    const NAME = 'mpReviewByCustomer';

    /**
     * @var string
     */
    protected $_template = 'Mageplaza_BetterProductReviews::dashboard/review-by-customer.phtml';

    /**
     * @var CollectionFactory
     */
    protected $_reviewsCtmColFact;

    /**
     * ReviewByCustomer constructor.
     *
     * @param Template\Context $context
     * @param DateTime $dateTime ,
     * @param CollectionFactory $reviewsCtmColFact
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        DateTime $dateTime,
        CollectionFactory $reviewsCtmColFact,
        array $data = []
    ) {
        $this->_reviewsCtmColFact = $reviewsCtmColFact;

        parent::__construct($context, $dateTime, $data);
    }

    /**
     * @return Collection
     * @throws Exception
     */
    public function getCollection()
    {
        /**
         * @var Collection $collection
         */
        $collection = $this->_reviewsCtmColFact->create()->setOrder('review_cnt', 'desc');
        $collection = $this->addDateFilter($collection, 'main_table.created_at');

        return $collection->setPageSize(5);
    }

    /**
     * @return Phrase|string
     */
    public function getTitle()
    {
        return __('Review by Customers');
    }

    /**
     * @return string
     */
    public function getTableId()
    {
        return 'review_by_customer_table';
    }

    /**
     * {@inheritdoc}
     */
    public function getRowUrl()
    {
        return $this->getUrl('reports/report_review/customer');
    }

    /**
     * @return bool
     */
    public function canShowDetail()
    {
        return true;
    }
}
