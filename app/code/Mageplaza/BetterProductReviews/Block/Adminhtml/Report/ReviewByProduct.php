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
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Framework\Phrase;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Reports\Model\ResourceModel\Review\Product\CollectionFactory;

/**
 * Class ReviewByProduct
 *
 * @package Mageplaza\BetterProductReviews\Block\Adminhtml\Report
 */
class ReviewByProduct extends AbstractClass
{
    const NAME = 'mpReviewByProduct';

    /**
     * @var string
     */
    protected $_template = 'Mageplaza_BetterProductReviews::dashboard/review-by-product.phtml';

    /**
     * @var CollectionFactory
     */
    protected $_reviewsProdColFact;

    /**
     * ReviewByProduct constructor.
     *
     * @param Template\Context $context
     * @param DateTime $dateTime
     * @param CollectionFactory $reviewsProdColFact
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        DateTime $dateTime,
        CollectionFactory $reviewsProdColFact,
        array $data = []
    ) {
        $this->_reviewsProdColFact = $reviewsProdColFact;

        parent::__construct($context, $dateTime, $data);
    }

    /**
     * @return AbstractCollection
     * @throws Exception
     */
    public function getCollection()
    {
        /**
         * @var AbstractCollection $collection
         */
        $collection = $this->_reviewsProdColFact->create()->setOrder('review_cnt', 'desc');
        $collection = $this->addDateFilter($collection, 'created_at');

        return $collection->setPageSize(5);
    }

    /**
     * @return Phrase|string
     */
    public function getTitle()
    {
        return __('Review by Products');
    }

    /**
     * @return string
     */
    public function getTableId()
    {
        return 'review_by_product_table';
    }

    /**
     * {@inheritdoc}
     */
    public function getRowUrl()
    {
        return $this->getUrl('reports/report_review/product');
    }

    /**
     * @return bool
     */
    public function canShowDetail()
    {
        return true;
    }
}
