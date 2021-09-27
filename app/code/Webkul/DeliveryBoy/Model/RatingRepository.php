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
use Webkul\DeliveryBoy\Api\Data\RatingInterface;
use Magento\Framework\Api\ExtensibleDataObjectConverter;

class RatingRepository implements \Webkul\DeliveryBoy\Api\RatingRepositoryInterface
{
    /**
     * @var RatingFactory
     */
    protected $ratingFactory;
    
    /**
     * @var ResourceModel\Rating\CollectionFactory
     */
    protected $collectionFactory;
    
    /**
     * @var ResourceModel\Rating
     */
    protected $resourceModel;
    
    /**
     * @var array
     */
    protected $instancesById = [];
    
    /**
     * @var ExtensibleDataObjectConverter
     */
    protected $extensibleDataObjectConverter;

    /**
     * @param RatingFactory $ratingFactory
     * @param ResourceModel\Rating\CollectionFactory $collectionFactory
     * @param ResourceModel\Rating $resourceModel
     * @param ExtensibleDataObjectConverter $extensibleDataObjectConverter
     */
    public function __construct(
        RatingFactory $ratingFactory,
        ResourceModel\Rating\CollectionFactory $collectionFactory,
        ResourceModel\Rating $resourceModel,
        ExtensibleDataObjectConverter $extensibleDataObjectConverter
    ) {
        $this->ratingFactory = $ratingFactory;
        $this->collectionFactory = $collectionFactory;
        $this->resourceModel = $resourceModel;
        $this->extensibleDataObjectConverter = $extensibleDataObjectConverter;
    }
    
    /**
     * @param RatingInterface $rating
     * @return RatingInterface
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function save(RatingInterface $rating)
    {
        $ratingId = $rating->getId();
        try {
            $this->resourceModel->save($rating);
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\CouldNotSaveException($e->getMessage());
        }
        unset($this->instancesById[$rating->getId()]);
        return $this->getById($rating->getId());
    }

    /**
     * @param int $ratingId
     * @return RatingInterface
     */
    public function getById($ratingId)
    {
        $ratingData = $this->ratingFactory->create();
        $ratingData->load($ratingId);
        $this->instancesById[$ratingId] = $ratingData;
        return $this->instancesById[$ratingId];
    }

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return ResourceModel\Rating\Collection
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        $collection = $this->collectionFactory->create();
        $collection->load();
        return $collection;
    }

    /**
     * @param RatingInterface $rating
     * @return bool
     * @throws \Magento\Framework\Exception\StateException
     */
    public function delete(RatingInterface $rating)
    {
        $ratingId = $rating->getId();
        try {
            $this->resourceModel->delete($rating);
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\StateException(__("Unable to remove rating with id %1", $ratingId));
        }
        unset($this->instancesById[$ratingId]);
        return true;
    }

    /**
     * @param int $rating
     * @return bool
     * @throws \Magento\Framework\Exception\StateException
     */
    public function deleteById($ratingId)
    {
        $rating = $this->getById($ratingId);
        return $this->delete($rating);
    }
}
