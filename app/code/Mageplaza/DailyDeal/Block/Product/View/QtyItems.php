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

namespace Mageplaza\DailyDeal\Block\Product\View;

use Mageplaza\DailyDeal\Block\Deal;
use Mageplaza\DailyDeal\Helper\Data as HelperData;

/**
 * Class QtyItems
 * @package Mageplaza\DailyDeal\Block\Product\View
 */
class QtyItems extends Deal
{
    /**
     * is show Qty of Remaining Items config
     *
     * @return mixed
     */
    public function isShowQtyRemain()
    {
        return $this->_helperData->getConfigGeneral('show_qty_remain');
    }

    /**
     * is Show Qty of Sold Items config
     *
     * @return mixed
     */
    public function isShowQtySold()
    {
        return $this->_helperData->getConfigGeneral('show_qty_sold');
    }

    /**
     * Get Qty of Remaining Items
     *
     * @param $productId
     *
     * @return mixed
     */
    public function getQtyRemain($productId)
    {
        $dealCollection = $this->_helperData->getProductDeal($productId);

        return $dealCollection->getDealQty() - $dealCollection->getSaleQty();
    }

    /**
     * Get Qty of Sold Items
     *
     * @param $productId
     *
     * @return mixed
     */
    public function getQtySold($productId)
    {
        $qty = $this->_helperData->getProductDeal($productId)->getSaleQty();

        return $qty ?: 0;
    }

    /**
     * @param $productId
     *
     * @return bool
     */
    public function isSimpleProduct($productId)
    {
        $product = $this->_helperData->_productFactory->create()->load($productId);

        return $product->getTypeId() === 'simple' || $product->getTypeId() === 'virtual';
    }

    /**
     * Send Qty data to js
     *
     * @param $productId
     *
     * @return string
     */
    public function getQtyData($productId)
    {
        $params = [
            'isSimpleProduct' => $this->isSimpleProduct($productId),
            'prodId'          => $productId,
            'qtyRemain'       => $this->getQtyRemain($productId),
            'qtySold'         => $this->getQtySold($productId),
            'qtyUrl'          => $this->getUrl('dailydeal/deal/qty'),
        ];

        return HelperData::jsonEncode($params);
    }
}
