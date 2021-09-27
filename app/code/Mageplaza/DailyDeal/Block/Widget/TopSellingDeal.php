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

use Exception;
use Magento\Catalog\Model\ResourceModel\Product\Collection;

/**
 * Class TopSellingDeal
 * @package Mageplaza\DailyDeal\Block\Widget
 */
class TopSellingDeal extends AbstractDeal
{
    /**
     * Get Top Selling Deal Product Collection
     *
     * @param null $limit
     *
     * @return array|Collection
     */
    public function getProductCollection($limit = null)
    {
        $productIds = $this->getProductIdsSellingDeal();

        return $this->getDealProducts($productIds, $limit, 'bestsell');
    }

    /**
     * Get Product Ids of Selling Product
     *
     * @return array
     * @throws Exception
     */
    public function getProductIdsSellingDeal()
    {
        $dealCollection = $this->_dealFactory->create()->getCollection()->setOrder('sale_qty', 'DESC');

        return $this->getProductDealIds($dealCollection);
    }

    /**
     * Get Block Title in Config
     *
     * @return mixed|string
     */
    public function getBlockTitle()
    {
        $title = $this->_helperData->getModuleConfig('sidebar_widget/selling_deal/title');

        return $title ?: '';
    }

    /**
     * Get limit product shown
     *
     * @return int|mixed
     */
    public function getLimit()
    {
        $limit = $this->_helperData->getModuleConfig('sidebar_widget/selling_deal/limit');

        return $limit ?: 1;
    }

    /**
     * Get Type deal
     *
     * @return string
     */
    public function getTypeWidget()
    {
        return 'selling';
    }
}
