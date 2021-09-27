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
 * @package     Mageplaza_DailyDeal
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\DailyDeal\Block\Widget;

use Magento\Catalog\Model\ResourceModel\Product\Collection;

/**
 * Class UpcomingDeal
 * @package Mageplaza\DailyDeal\Block\Widget
 */
class UpcomingDeal extends AbstractDeal
{
    /**
     * Get Upcoming Deal Product Collection
     *
     * @param $limit
     *
     * @return array|Collection
     */
    public function getProductCollection($limit)
    {
        $productIds = $this->getProductIdsUpcomingDeal();

        return $this->getDealProducts($productIds, $limit, 'updeal');
    }

    /**
     * Get Product Ids of Upcoming Deal Product
     *
     * @return array
     */
    public function getProductIdsUpcomingDeal()
    {
        $dealCollection = $this->_dealFactory->create()->getCollection()->setOrder('date_from', 'ASC');

        return $this->getUpcomingDealIds($dealCollection);
    }

    /**
     * Check product is upcoming product
     *
     * @param $productId
     *
     * @return bool
     */
    public function checkUpcomingDeal($productId)
    {
        $dealCollection = $this->_helperData->getProductDeal($productId);
        $currentDate    = date('d-m-Y H:i:s');
        $status         = $dealCollection->getStatus();
        $dateFrom       = $dealCollection->getDateFrom();

        return $status && strtotime($dateFrom) >= strtotime($currentDate);
    }

    /**
     * Get Product Ids of upcoming deal
     *
     * @param $collection
     *
     * @return array
     */
    public function getUpcomingDealIds($collection)
    {
        $productIds = [];
        foreach ($collection as $item) {
            $productId = $item->getProductId();
            if ($this->checkUpcomingDeal($productId)) {
                $productIds[] = $productId;
            }
        }

        return $productIds;
    }

    /**
     * Get Block Title in Config
     *
     * @return mixed|string
     */
    public function getBlockTitle()
    {
        $title = $this->_helperData->getModuleConfig('sidebar_widget/upcoming_deal/title');

        return $title ?: '';
    }

    /**
     * Get limit product shown
     *
     * @return int|mixed
     */
    public function getLimit()
    {
        $limit = $this->_helperData->getModuleConfig('sidebar_widget/upcoming_deal/limit');

        return $limit ?: 1;
    }

    /**
     * Get Type deal
     *
     * @return string
     */
    public function getTypeWidget()
    {
        return 'upcoming';
    }
}
