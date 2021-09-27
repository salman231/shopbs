<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magedelight\MembershipSubscription\Api\Data;

/**
 * Interface LinkInterface
 * @api
 * @since 100.0.2
 */
interface DurationInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    const DURATION = 'duration';
    const DURATION_UNIT = 'duration_unit';
    const PRICE = 'price';
    const SORT_ORDER = 'sort_order';

    /**
     * Get Duration
     * @return string|null
     */
    public function getDuration();

    /**
     * Set Duration
     * @param string $duration
     * @return \Magedelight\MembershipSubscription\Api\Data\DurationInterface
     */
    public function setDuration($duration);

    /**
     * Get Duration Unit
     * @return string|null
     */
    public function getDurationUnit();

    /**
     * Set Duration Unit
     * @param $durationUnit
     * @return \Magedelight\MembershipSubscription\Api\Data\DurationInterface
     */
    public function setDurationUnit($durationUnit);

    /**
     * Get Price
     * @return float|int
     */
    public function getPrice();

    /**
     * Set Price
     * @param $price
     * @return \Magedelight\MembershipSubscription\Api\Data\DurationInterface
     */
    public function setPrice($price);


    /**
     * Get sort order
     * @return int
     */
    public function getSortOrder();

    /**
     * Set Sort Order
     * @param $sortOrder
     * @return \Magedelight\MembershipSubscription\Api\Data\DurationInterface
     */
    public function setSortOrder($sortOrder);
}
