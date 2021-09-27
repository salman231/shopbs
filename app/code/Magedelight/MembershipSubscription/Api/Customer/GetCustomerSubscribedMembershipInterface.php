<?php
namespace Magedelight\MembershipSubscription\Api\Customer;
/**
* Copyright © Magento, Inc. All rights reserved.
* See COPYING.txt for license details.
*/

/**
* @api
*/
interface GetCustomerSubscribedMembershipInterface
{
  /**
   * Get all subscribed membership of customer by customer Id
   *
   * @param int $cid
   * @return string[]
   * @throws NoSuchEntityException
   */
  public function getcustomersubscribedMembership($cid);
}