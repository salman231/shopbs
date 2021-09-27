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
 * Class FloatDeal
 * @package Mageplaza\DailyDeal\Block\Widget
 */
class FloatDeal extends AbstractDeal
{
    /**
     * Get Random Deal Product Collection
     *
     * @return Collection
     * @throws Exception
     */
    public function getProductCollection()
    {
        $productIds = $this->getProductIdsRandomDeal();

        return $this->getDealProducts($productIds, $this->getLimit(), 'random');
    }

    /**
     * check is enable Float slider deal
     *
     * @return mixed
     */
    public function isFloatEnable()
    {
        return $this->_helperData->getModuleConfig('sidebar_widget/float_deal/enable');
    }

    /**
     * is show on mobile
     *
     * @return mixed
     */
    public function isShowOnMobile()
    {
        return $this->_helperData->getModuleConfig('sidebar_widget/float_deal/show_mobile');
    }

    /**
     * Get Block Title in Config
     *
     * @return mixed|string
     */
    public function getBlockTitle()
    {
        $title = $this->_helperData->getModuleConfig('sidebar_widget/float_deal/title');

        return $title ?: '';
    }

    /**
     * Get limit product shown
     *
     * @return int|mixed
     */
    public function getLimit()
    {
        $limit = $this->_helperData->getModuleConfig('sidebar_widget/float_deal/limit');

        return $limit ?: 1;
    }

    /**
     * Get Position to show float
     *
     * @return mixed
     */
    public function getPositionFloat()
    {
        return $this->_helperData->getModuleConfig('sidebar_widget/float_deal/show_on');
    }

    /**
     * Get Time show again floating
     *
     * @return int|mixed
     */
    public function getTimeShowAgain()
    {
        $config = $this->_helperData->getModuleConfig('sidebar_widget/float_deal/show_again_after');

        return $config ?: 0;
    }
}
