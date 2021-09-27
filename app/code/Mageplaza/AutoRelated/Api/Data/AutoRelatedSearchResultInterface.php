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
 * @package     Mageplaza_AutoRelated
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\AutoRelated\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * Interface AutoRelatedSearchResultInterface
 * @package Mageplaza\AutoRelated\Api\Data
 */
interface AutoRelatedSearchResultInterface extends SearchResultsInterface
{
    /**
     * Get deal list.
     *
     * @return \Mageplaza\AutoRelated\Api\Data\AutoRelatedInterface[]
     */
    public function getItems();

    /**
     * Set deal list.
     *
     * @param \Mageplaza\AutoRelated\Api\Data\AutoRelatedInterface[] $items
     *
     * @return $this
     */
    public function setItems(array $items);
}
