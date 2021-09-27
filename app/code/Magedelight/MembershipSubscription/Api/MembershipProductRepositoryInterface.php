<?php
/**
 * Magedelight
 * Copyright (C) 2017 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_MembershipSubscription
 * @copyright Copyright (c) 2017 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */

namespace Magedelight\MembershipSubscription\Api;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magedelight\MembershipSubscription\Api\Data\MembershipProductSearchResultsInterface;

/**
 * @api
 */
interface MembershipProductRepositoryInterface
{

    /**
     * save membership product
     * @param \Magedelight\MembershipSubscription\Api\Data\MembershipProductInterface $membershipProduct
     * @return \Magedelight\MembershipSubscription\Api\Data\MembershipProductInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(\Magedelight\MembershipSubscription\Api\Data\MembershipProductInterface $membershipProduct);

    /**
     * Retrieve membership product
     * @param string $membershipProductId
     * @return \Magedelight\MembershipSubscription\Api\Data\MembershipProductInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($membershipProductId);
}
