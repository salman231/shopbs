<?php

namespace Magedelight\MembershipSubscription\Api\Data;

/**
 * @api
 */
interface MembershipProductInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const MEMBERSHIP_PRODUCT_ID = 'membership_product_id';
    const PRODUCT_NAME = 'product_name';
    const PRODUCT_ID = 'product_id';
    const CUSTOMER_GROUP_ID = 'customer_group_id';
    const RELATED_CUSTOMER_GROUP_ID = 'related_customer_group_id';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';


    /**
     * Get Membership Product Id
     * @return int|null
     */
    public function getMembershipProductId();


    /**
     * Get Product Name
     * @return string
     */
    public function getProductName();


    /**
     * Get Product Id
     * @return int|null
     */
    public function getProductId();


    /**
     * Get customer group id
     * @return int|null
     */
    public function getCustomerGroupId();


    /**
     * Get related customer group id
     * @return int|null
     */
    public function getRelatedCustomerGroupId();


    /**
     * Get created at
     * @return string
     */
    public function getCreatedAt();


    /**
     * Get updated at
     * @return string
     */
    public function getUpdatedAt();


    /**
     * Set membership product id
     * @param $membershipProductId
     * @return \Magedelight\MembershipSubscription\Api\Data\MembershipProductInterface
     */
    public function setMembershipProductId($membershipProductId);


    /**
     * Set product name
     * @param $productName
     * @return \Magedelight\MembershipSubscription\Api\Data\MembershipProductInterface
     */
    public function setProductName($productName);


    /**
     * Set product Id
     * @param $productId
     * @return \Magedelight\MembershipSubscription\Api\Data\MembershipProductInterface
     */
    public function setProductId($productId);


    /**
     * Set customer group id
     * @param $customerGroupId
     * @return \Magedelight\MembershipSubscription\Api\Data\MembershipProductInterface
     */
    public function setCustomerGroupId($customerGroupId);


    /**
     * Set related customer group
     * @param $relatedCustomerGroupId
     * @return \Magedelight\MembershipSubscription\Api\Data\MembershipProductInterface
     */
    public function setRelatedCustomerGroupId($relatedCustomerGroupId);


    /**
     * Set created at
     * @param $createdAt
     * @return \Magedelight\MembershipSubscription\Api\Data\MembershipProductInterface
     */
    public function setCreatedAt($createdAt);


    /**
     * Set updated at
     * @param $updatedAt
     * @return \Magedelight\MembershipSubscription\Api\Data\MembershipProductInterface
     */
    public function setUpdatedAt($updatedAt);
}
