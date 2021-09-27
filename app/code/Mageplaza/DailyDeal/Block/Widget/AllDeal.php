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
 * Class AllDeal
 * @package Mageplaza\DailyDeal\Block\Widget
 */
class AllDeal extends AbstractDeal
{
    /**
     * Get All Deal Product Collection
     *
     * @param null $limit
     *
     * @return array|Collection
     * @throws Exception
     */
    public function getProductCollection($limit = null)
    {
        $productIds = $this->getProductIdsRandomDeal();

        return $this->getDealProducts($productIds, $limit);
    }
}
