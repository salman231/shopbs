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
 * Interface for rma conversation search results.
 * @api
 */
interface ConversationSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{
    /**
     * Get conversation list.
     *
     * @return \Webkul\Rmasystem\Api\Data\ConversationInterface[]
     */
    public function getItems();

    /**
     * Set conversation list.
     *
     * @api
     * @param \Webkul\Rmasystem\Api\Data\ConversationInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
