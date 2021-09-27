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
namespace Webkul\DeliveryBoy\Model;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\ExtensibleDataObjectConverter;
use Webkul\DeliveryBoy\Api\Data\DeliveryboyInterface;

class DeliveryboyRepository implements \Webkul\DeliveryBoy\Api\DeliveryboyRepositoryInterface
{
    /**
     * @var array
     */
    protected $instancesById = [];

    /**
     * @var ResourceModel\Deliveryboy $resourceModel
     */
    protected $resourceModel;

    /**
     * @var ResourceModel\Deliveryboy\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var DeliveryboyFactory
     */
    protected $deliveryboyFactory;

    /**
     * @var ExtensibleDataObjectConverter
     */
    protected $extensibleDataObjectConverter;

    /**
     * @param ResourceModel\Deliveryboy $resourceModel
     * @param DeliveryboyFactory $deliveryboyFactory
     * @param ResourceModel\Deliveryboy\CollectionFactory $collectionFactory
     * @param ExtensibleDataObjectConverter $extensibleDataObjectConverter
     */
    public function __construct(
        ResourceModel\Deliveryboy $resourceModel,
        DeliveryboyFactory $deliveryboyFactory,
        ResourceModel\Deliveryboy\CollectionFactory $collectionFactory,
        ExtensibleDataObjectConverter $extensibleDataObjectConverter
    ) {
        $this->resourceModel = $resourceModel;
        $this->collectionFactory = $collectionFactory;
        $this->deliveryboyFactory = $deliveryboyFactory;
        $this->extensibleDataObjectConverter = $extensibleDataObjectConverter;
    }

    /**
     * @param DeliveryboyInterface $deliveryboy
     * @return DeliveryboyInterface
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function save(DeliveryboyInterface $deliveryboy)
    {
        $deliveryboyId = $deliveryboy->getId();
        try {
            $this->resourceModel->save($deliveryboy);
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\CouldNotSaveException(__($e->getMessage()));
        }
        unset($this->instancesById[$deliveryboy->getId()]);
        return $this->getById($deliveryboy->getId());
    }

    /**
     * @param int $deliveryboyId
     * @return DeliveryboyInterface
     */
    public function getById($deliveryboyId)
    {
        $deliveryboyData = $this->deliveryboyFactory->create();
        $deliveryboyData->load($deliveryboyId);
        $this->instancesById[$deliveryboyId] = $deliveryboyData;
        return $this->instancesById[$deliveryboyId];
    }

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return ResourceModel\Deliveryboy\Collection
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        $collection = $this->collectionFactory->create();
        $collection->load();
        return $collection;
    }

    /**
     * @param DeliveryboyInterface $deliveryboy
     * @return bool
     * @throws \Magento\Framework\Exception\StateException
     */
    public function delete(DeliveryboyInterface $deliveryboy)
    {
        $deliveryboyId = $deliveryboy->getId();
        try {
            $this->resourceModel->delete($deliveryboy);
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\StateException(
                __("Unable to remove deliveryboy with id %1", $deliveryboyId)
            );
        }
        unset($this->instancesById[$deliveryboyId]);
        return true;
    }

    /**
     * @param int $deliveryboyId
     * @return bool
     * @throws \Magento\Framework\Exception\StateException
     */
    public function deleteById($deliveryboyId)
    {
        $deliveryboy = $this->getById($deliveryboyId);
        return $this->delete($deliveryboy);
    }
}
