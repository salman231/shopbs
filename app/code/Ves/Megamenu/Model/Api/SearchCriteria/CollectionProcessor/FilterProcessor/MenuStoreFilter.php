<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Ves\Megamenu\Model\Api\SearchCriteria\CollectionProcessor\FilterProcessor;

use Magento\Framework\Api\Filter;
use Magento\Framework\Api\SearchCriteria\CollectionProcessor\FilterProcessor\CustomFilterInterface;
use Magento\Framework\Data\Collection\AbstractDb;

class MenuStoreFilter implements CustomFilterInterface
{
    /**
     * Apply custom store filter to collection
     *
     * @param Filter $filter
     * @param AbstractDb $collection
     * @return bool
     */
    public function apply(Filter $filter, AbstractDb $collection)
    {
        /** @var \Ves\Megamenu\Model\ResourceModel\Menu\Collection $collection */
        $collection->addStoreFilter($filter->getValue(), false);

        return true;
    }
}
