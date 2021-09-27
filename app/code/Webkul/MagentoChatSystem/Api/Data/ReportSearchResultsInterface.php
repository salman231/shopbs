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

interface ReportSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{

    /**
     * Get Report list.
     * @return \Webkul\MagentoChatSystem\Api\Data\ReportInterface[]
     */
    public function getItems();

    /**
     * Set customer_id list.
     * @param \Webkul\MagentoChatSystem\Api\Data\ReportInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
