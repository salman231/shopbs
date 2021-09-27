<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Rmasystem
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Rmasystem\Api\Data;

/**
 * Interface for all rma search results.
 * @api
 */
interface AllrmaSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{
    /**
     * Get all rma list.
     *
     * @return \Webkul\Rmasystem\Api\Data\AllrmaInterface[]
     */
    public function getItems();

    /**
     * Set all rma list.
     *
     * @api
     * @param \Webkul\Rmasystem\Api\Data\AllrmaInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
