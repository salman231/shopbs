<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MagentoChatSystem
 * @author    Webkul
 * @copyright Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MagentoChatSystem\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * Interface customer data search results.
 * @api
 */
interface CustomerDataSearchResultInterface extends SearchResultsInterface
{
    /**
     * Get customer data list.
     *
     * @return \Webkul\MagentoChatSystem\Api\Data\CustomerDataInterface[]
     */
    public function getItems();

    /**
     * Set customer data list.
     *
     * @param \Webkul\MagentoChatSystem\Api\Data\CustomerDataInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
