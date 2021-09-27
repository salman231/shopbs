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

use DateTimeZone;
use Exception;
use Magento\Backend\Block\Template;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Framework\Stdlib\DateTime\DateTime;

/**
 * Class AbstractClass
 *
 * @package Mageplaza\BetterProductReviews\Block\Adminhtml\Report
 * @method  setArea(string $string)
 */
abstract class AbstractClass extends Template
{
    const NAME = '';
    const MAGE_REPORT_CLASS = '';

    /**
     * @var DateTime
     */
    protected $_dateTime;

    /**
     * AbstractClass constructor.
     *
     * @param Template\Context $context
     * @param DateTime $dateTime
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        DateTime $dateTime,
        array $data = []
    ) {
        $this->setArea('adminhtml');
        $this->_dateTime = $dateTime;

        parent::__construct($context, $data);
    }

    /**
     * @inheritdoc
     */
    public function getContentHtml()
    {
        if (static::MAGE_REPORT_CLASS) {
            return $this->getLayout()->createBlock(static::MAGE_REPORT_CLASS)->setArea('adminhtml')
                ->toHtml();
        }

        return $this->toHtml();
    }

    /**
     * @param AbstractCollection $collection
     * @param string $dateCol
     *
     * @return mixed
     * @throws Exception
     */
    public function addDateFilter($collection, $dateCol)
    {
        $dateRange = $this->getDateRange();
        $start = $this->convertTimeZone($dateRange[0])->format('Y-m-d H:i:s');
        $end = $this->convertTimeZone($dateRange[1])->format('Y-m-d H:i:s');
        $collection->addFieldToFilter($dateCol, ['from' => $start]);
        $collection->addFieldToFilter($dateCol, ['to' => $end]);

        return $collection;
    }

    /**
     * @return array
     */
    public function getDateRange()
    {
        if ($dateRange = $this->_request->getParam('dateRange')) {
            $fromDate = $this->_dateTime->date('m/d/Y', $dateRange[0]);
            $toDate = $this->_dateTime->date('m/d/Y', $dateRange[1]);
        } else {
            $toDate = $this->_dateTime->date('m/d/Y');
            $fromDate = $this->_dateTime->date('m/d/Y', $toDate . '-1 month');
        }

        return [$fromDate, $toDate];
    }

    /**
     * @param string $date
     *
     * @return \DateTime
     * @throws Exception
     */
    public function convertTimeZone($date)
    {
        $dateTime = new \DateTime($date, new DateTimeZone('UTC'));
        $dateTime->setTimezone(new DateTimeZone($this->_localeDate->getConfigTimezone()));

        return $dateTime;
    }

    /**
     * @return bool
     */
    public function canShowDetail()
    {
        return false;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return '';
    }

    /**
     * @return string
     */
    public function getName()
    {
        return static::NAME;
    }

    /**
     * @return string
     */
    public function getTotal()
    {
        return '';
    }
}
