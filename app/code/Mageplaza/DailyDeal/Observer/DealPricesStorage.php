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

namespace Mageplaza\DailyDeal\Observer;

/**
 * Class DealPricesStorage
 * @package Mageplaza\DailyDeal\Observer
 */
class DealPricesStorage
{
    /**
     * Store calculated daily deal prices for products
     * Prices collected per store, date and product
     *
     * @var array
     */
    private $rulePrices = [];

    /**
     * @param string $id
     *
     * @return false|float
     */
    public function getDealPrice($id)
    {
        return isset($this->rulePrices[$id]) ? $this->rulePrices[$id] : false;
    }

    /**
     * @param string $id
     *
     * @return bool
     */
    public function hasDealPrice($id)
    {
        return isset($this->rulePrices[$id]);
    }

    /**
     * @param string $id
     * @param float $price
     *
     * @return void
     */
    public function setDealPrice($id, $price)
    {
        $this->rulePrices[$id] = $price;
    }
}
