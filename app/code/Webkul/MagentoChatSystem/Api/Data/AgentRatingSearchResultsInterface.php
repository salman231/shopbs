<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MagentoChatSystem
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MagentoChatSystem\Api\Data;

interface AgentRatingSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{

    /**
     * Get AgentRating list.
     * @return \Webkul\MagentoChatSystem\Api\Data\AgentRatingInterface[]
     */
    public function getItems();

    /**
     * Set agent_id list.
     * @param \Webkul\MagentoChatSystem\Api\Data\AgentRatingInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
