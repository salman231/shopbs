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

use Magento\Framework\Api\SearchCriteriaInterface;
use Webkul\DeliveryBoy\Api\Data\DeliveryboyInterface;

interface DeliveryboyRepositoryInterface
{
    /**
     * @param DeliveryboyInterface $deliveryboy
     * @return DeliveryboyInterface
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function save(DeliveryboyInterface $deliveryboy);

    /**
     * @param int $deliveryboyId
     * @return DeliveryboyInterface
     */
    public function getById($deliveryboyId);

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return \Webkul\DeliveryBoy\Model\ResourceModel\Deliveryboy\Collection
     */
    public function getList(SearchCriteriaInterface $searchCriteria);

    /**
     * @param DeliveryboyInterface $deliveryboy
     * @return bool
     * @throws \Magento\Framework\Exception\StateException
     */
    public function delete(DeliveryboyInterface $deliveryboy);

    /**
     * @param int $deliveryboyId
     * @return bool
     * @throws \Magento\Framework\Exception\StateException
     */
    public function deleteById($deliveryboyId);
}
