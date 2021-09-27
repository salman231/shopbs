<?php
/**
 * Webkul Software.
 *
 * PHP version 7.0+
 *
 * @category  Webkul
 * @package   Webkul_DeliveryBoy
 * @author    Webkul <support@webkul.com>
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html ASL Licence
 * @link      https://store.webkul.com/license.html
 */
namespace Webkul\DeliveryBoy\Api;

use Webkul\DeliveryBoy\Api\Data\OrderInterface;
use Magento\Framework\Api\SearchCriteriaInterface;

interface OrderRepositoryInterface
{
    /**
     * @param int $id
     * @return OrderInterface
     */
    public function getById($id);

    /**
     * @param int $id
     * @return OrderInterface
     * @throws \Magento\Framework\Exception\StateException
     */
    public function deleteById($id);

    /**
     * @param OrderInterface $deliveryboy
     * @return OrderInterface
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function save(OrderInterface $deliveryboy);

    /**
     * @param OrderInterface $deliveryboy
     * @return bool
     * @throws \Magento\Framework\Exception\StateException
     */
    public function delete(OrderInterface $deliveryboy);

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return \Webkul\DeliveryBoy\Model\ResourceModel\Order\Collection
     */
    public function getList(SearchCriteriaInterface $searchCriteria);
}
