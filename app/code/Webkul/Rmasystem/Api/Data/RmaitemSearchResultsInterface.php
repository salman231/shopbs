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
 * Interface for returned items search results.
 * @api
 */
interface RmaitemSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{
    /**
     * Get returned items list.
     *
     * @return \Webkul\Rmasystem\Api\Data\RmaitemInterface[]
     */
    public function getItems();

    /**
     * Set returned items list.
     *
     * @api
     * @param \Webkul\Rmasystem\Api\Data\RmaitemInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
