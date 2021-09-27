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
 * Class NewDeal
 * @package Mageplaza\DailyDeal\Block\Widget
 */
class NewDeal extends AbstractDeal
{
    /**
     * Get New Deal Product Collection
     *
     * @param null $limit
     *
     * @return array|Collection
     */
    public function getProductCollection($limit = null)
    {
        $productIds = $this->getProductIds();

        return $this->getDealProducts($productIds, $limit, 'newdeal');
    }

    /**
     * Get Product Ids of New Deal
     *
     * @return array
     * @throws Exception
     */
    public function getProductIds()
    {
        $dealCollection = $this->_dealFactory->create()->getCollection()->setOrder('date_from');

        return $this->getProductDealIds($dealCollection);
    }
}
