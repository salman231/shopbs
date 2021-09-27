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
 * Interface message search results.
 * @api
 */
interface AgentDataSearchResultInterface extends SearchResultsInterface
{
    /**
     * Get message list.
     *
     * @return \Webkul\MagentoChatSystem\Api\Data\AgentDataInterface[]
     */
    public function getItems();

    /**
     * Set message list.
     *
     * @param \Webkul\MagentoChatSystem\Api\Data\AgentDataInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
