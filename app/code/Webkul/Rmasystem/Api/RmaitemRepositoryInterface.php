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
namespace Webkul\Rmasystem\Api;

/**
 * Rma item CRUD interface
 * @api
 */
interface RmaitemRepositoryInterface
{
    /**
     * Save RMA item.
     *
     * @param Webkul\Rmasystem\Api\Data\RmaitemInterface $rmaItem
     * @return Webkul\Rmasystem\Api\Data\RmaitemInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException If a RMA ID is sent but the rma does not exist
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(\Webkul\Rmasystem\Api\Data\RmaitemInterface $rmaItem);

    /**
     * Get RMA item by ID.
     *
     * @param int $id
     * @return Webkul\Rmasystem\Api\Data\RmaitemInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException If $rmaId is not found
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($id);

    /**
     * Retrieve rma item list.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return Webkul\Rmasystem\Api\Data\RmaitemSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);

    /**
     * Delete rma item.
     *
     * @param Webkul\Rmasystem\Api\Data\RmaitemInterface $group
     * @return bool true on success
     * @throws \Magento\Framework\Exception\StateException If rma cannot be deleted
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(\Webkul\Rmasystem\Api\Data\RmaitemInterface $rmaItem);

    /**
     * Delete RMA item by ID.
     *
     * @param int $id
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\StateException If rma cannot be deleted
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($id);
}
