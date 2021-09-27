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
 * Rma reason CRUD interface
 * @api
 */
interface ReasonRepositoryInterface
{
    /**
     * Save Reason.
     *
     * @param Webkul\Rmasystem\Api\Data\ReasonInterface $reason
     * @return Webkul\Rmasystem\Api\Data\ReasonInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException If a RMA reason ID is sent but it does not exist
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(\Webkul\Rmasystem\Api\Data\ReasonInterface $reason);

    /**
     * Get reason by rma ID.
     *
     * @param int $id
     * @return Webkul\Rmasystem\Api\Data\ReasonInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException If $rmaId is not found
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($id);

    /**
     * Retrieve rma reason list.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return Webkul\Rmasystem\Api\Data\ReasonSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);

    /**
     * Delete rma reason.
     *
     * @param Webkul\Rmasystem\Api\Data\ReasonInterface $group
     * @return bool true on success
     * @throws \Magento\Framework\Exception\StateException If rma reason cannot be deleted
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(\Webkul\Rmasystem\Api\Data\ReasonInterface $group);

    /**
     * Delete rma reason by ID.
     *
     * @param int $id
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\StateException If rma reason cannot be deleted
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($id);
}
