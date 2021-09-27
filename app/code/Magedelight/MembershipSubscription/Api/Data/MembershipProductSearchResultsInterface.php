<?php

namespace Magedelight\MembershipSubscription\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * @api
 */
interface MembershipProductSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get membership product list
     * @return \Magedelight\MembershipSubscription\Api\Data\MembershipProductInterface[]
     */
    public function getItems();

    /**
     * Set membership product list.
     * @param \Magedelight\MembershipSubscription\Api\Data\MembershipProductInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
