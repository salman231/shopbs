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
 * Interface for rma reason search results.
 * @api
 */
interface ReasonSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{
    /**
     * Get reason list.
     *
     * @return \Webkul\Rmasystem\Api\Data\ReasonInterface[]
     */
    public function getItems();

    /**
     * Set reason list.
     *
     * @api
     * @param \Webkul\Rmasystem\Api\Data\ReasonInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
