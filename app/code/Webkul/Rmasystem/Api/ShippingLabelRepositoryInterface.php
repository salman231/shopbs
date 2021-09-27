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
interface ShippingLabelRepositoryInterface
{
    /**
     * Save RMA.
     *
     * @param Webkul\Rmasystem\Api\Data\ShippinglabelInterface $shippingLabel
     * @return Webkul\Rmasystem\Api\Data\ShippinglabelInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException If a RMA ID is sent but the rma does not exist
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(\Webkul\Rmasystem\Api\Data\ShippinglabelInterface $shippingLabel);

    /**
     * Get RMA shipping label by ID.
     *
     * @param int $id
     * @return Webkul\Rmasystem\Api\Data\ShippinglabelInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException If $rmaId is not found
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($id);

    /**
     * Retrieve rma shipping label list.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return Webkul\Rmasystem\Api\Data\ShippinglabelSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);

    /**
     * Delete rma shipping label.
     *
     * @param Webkul\Rmasystem\Api\Data\ShippinglabelInterface $group
     * @return bool true on success
     * @throws \Magento\Framework\Exception\StateException If rma cannot be deleted
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(\Webkul\Rmasystem\Api\Data\ShippinglabelInterface $group);

    /**
     * Delete RMA shipping label by ID.
     *
     * @param int $id
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\StateException If rma cannot be deleted
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($id);
}
