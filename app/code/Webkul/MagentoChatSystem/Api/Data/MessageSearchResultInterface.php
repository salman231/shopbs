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
interface MessageSearchResultInterface extends SearchResultsInterface
{
    /**
     * Get message list.
     *
     * @return \Webkul\MpTimeDelivery\Api\Data\TimeslotConfigInterface[]
     */
    public function getItems();

    /**
     * Set message list.
     *
     * @param \Webkul\MpTimeDelivery\Api\Data\TimeslotConfigInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
