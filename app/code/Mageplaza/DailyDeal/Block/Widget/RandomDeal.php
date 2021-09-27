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
 * Class RandomDeal
 * @package Mageplaza\DailyDeal\Block\Widget
 */
class RandomDeal extends AbstractDeal
{
    /**
     * Get Random Deal Product Collection
     *
     * @param $limit
     *
     * @return array|Collection
     * @throws Exception
     */
    public function getProductCollection($limit)
    {
        $productIds = $this->getProductIdsRandomDeal();

        return $this->getDealProducts($productIds, $limit, 'random');
    }

    /**
     * Get Block Title in Config
     *
     * @return mixed|string
     */
    public function getBlockTitle()
    {
        $title = $this->_helperData->getModuleConfig('sidebar_widget/random_deal/title');

        return $title ?: '';
    }

    /**
     * Get limit product shown
     *
     * @return int|mixed
     */
    public function getLimit()
    {
        $limit = $this->_helperData->getModuleConfig('sidebar_widget/random_deal/limit');

        return $limit ?: 1;
    }

    /**
     * Get Type deal
     *
     * @return string
     */
    public function getTypeWidget()
    {
        return 'random';
    }
}
