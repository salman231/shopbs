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

namespace Mageplaza\DailyDeal\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * Interface DealSearchResultInterface
 * @package Mageplaza\DailyDeal\Api\Data
 */
interface DealSearchResultInterface extends SearchResultsInterface
{
    /**
     * Get deal list.
     *
     * @return \Mageplaza\DailyDeal\Api\Data\DailyDealInterface[]
     */
    public function getItems();

    /**
     * Set deal list.
     *
     * @param \Mageplaza\DailyDeal\Api\Data\DailyDealInterface[] $items
     *
     * @return $this
     */
    public function setItems(array $items);
}
