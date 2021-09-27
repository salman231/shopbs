<?php

namespace Magedelight\MembershipSubscription\Api\Data;

/**
 * @api
 */
interface MembershipProductOptionInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    const MEMBERSHIP_DURATION = 'membership_duration';
    const  FEATURED = 'featured';

    /**
     * Get membership duration
     *
     * @return \Magedelight\MembershipSubscription\Api\Data\DurationInterface[] |null
     */
    public function getMembershipDuration();

    /**
     * Set Membership Duration
     * @param array|null $membershipDuration
     * @return \Magedelight\MembershipSubscription\Api\Data\DurationInterface[]
     */
    public function setMembershipDuration(array $membershipDuration = null);

    /**
     * Get featured
     * @return int
     */
    public function getFeatured();

    /**
     * Set featured
     * @param $featured
     * @return \Magedelight\MembershipSubscription\Api\Data\DurationInterface
     */
    public function setFeatured($featured);
}
