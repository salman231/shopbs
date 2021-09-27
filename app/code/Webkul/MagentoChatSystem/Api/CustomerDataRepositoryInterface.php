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
namespace Webkul\MagentoChatSystem\Api;

use Magento\Framework\Api\SearchCriteriaInterface;

/**
 * time slots block CRUD interface.
 * @api
 */
interface CustomerDataRepositoryInterface
{
    /**
     * Save customer data.
     *
     * @param \Webkul\MagentoChatSystem\Api\Data\CustomerDataInterface $items
     * @return \Webkul\MagentoChatSystem\Api\Data\CustomerDataInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(Data\CustomerDataInterface $items);

    /**
     * Retrieve customer data.
     *
     * @param int $id
     * @return \Webkul\MagentoChatSystem\Api\Data\CustomerDataInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($id);

    /**
     * Retrieve customer matching the specified criteria.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Webkul\MagentoChatSystem\Api\Data\CustomerDataSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);

    /**
     * Delete customer data.
     *
     * @param \Magento\Cms\Api\Data\PreorderItemsInterface $item
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(Data\CustomerDataInterface $item);

    /**
     * Delete customer.
     *
     * @param int $id
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($id);
}
