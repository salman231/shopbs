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
 * All rma CRUD interface
 * @api
 */
interface AllRmaRepositoryInterface
{
    /**
     * Save RMA.
     *
     * @param Webkul\Rmasystem\Api\Data\AllrmaInterface $allrma
     * @return Webkul\Rmasystem\Api\Data\AllrmaInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException If a RMA ID is sent but the rma does not exist
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(\Webkul\Rmasystem\Api\Data\AllrmaInterface $allrma);

    /**
     * Get RMA by rma ID.
     *
     * @param int $id
     * @return Webkul\Rmasystem\Api\Data\AllrmaInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException If $rmaId is not found
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($id);

    /**
     * Retrieve rma list.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return Webkul\Rmasystem\Api\Data\AllrmaSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);

    /**
     * Delete rma.
     *
     * @param Webkul\Rmasystem\Api\Data\AllrmaInterface $group
     * @return bool true on success
     * @throws \Magento\Framework\Exception\StateException If rma cannot be deleted
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(\Webkul\Rmasystem\Api\Data\AllrmaInterface $group);

    /**
     * Delete RMA by ID.
     *
     * @param int $id
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\StateException If rma cannot be deleted
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($id);
}
