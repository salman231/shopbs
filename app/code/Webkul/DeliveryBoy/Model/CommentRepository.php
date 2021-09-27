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

use Webkul\DeliveryBoy\Api\Data\CommentInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Api\ExtensibleDataObjectConverter;

class CommentRepository implements \Webkul\DeliveryBoy\Api\CommentRepositoryInterface
{
    /**
     * @var array
     */
    protected $instancesById = [];

    /**
     * @var ResourceModel\Comment
     */
    protected $resourceModel;
    
    /**
     * @var CommentFactory
     */
    protected $commentFactory;

    /**
     * @var ResourceModel\Comment\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var ExtensibleDataObjectConverter
     */
    protected $extensibleDataObjectConverter;

    /**
     * @param ResourceModel\Comment                   $resourceModel
     * @param CommentFactory                          $commentFactory
     * @param ResourceModel\Comment\CollectionFactory $collectionFactory
     * @param ExtensibleDataObjectConverter           $extensibleDataObjectConverter
     */
    public function __construct(
        ResourceModel\Comment $resourceModel,
        CommentFactory $commentFactory,
        ResourceModel\Comment\CollectionFactory $collectionFactory,
        ExtensibleDataObjectConverter $extensibleDataObjectConverter
    ) {
        $this->resourceModel = $resourceModel;
        $this->commentFactory = $commentFactory;
        $this->collectionFactory = $collectionFactory;
        $this->extensibleDataObjectConverter = $extensibleDataObjectConverter;
    }

    /**
     * @param  CommentInterface $comment
     * @return CommentInterface
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function save(CommentInterface $comment)
    {
        $commentId = $comment->getId();
        try {
            $this->resourceModel->save($comment);
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\CouldNotSaveException($e->getMessage());
        }
        unset($this->instancesById[$comment->getId()]);
        return $this->getById($comment->getId());
    }

    /**
     * @param  int $commentId
     * @return CommentInterface
     */
    public function getById($commentId)
    {
        $commentData = $this->commentFactory->create();
        $commentData->load($commentId);
        $this->instancesById[$commentId] = $commentData;
        return $this->instancesById[$commentId];
    }

    /**
     * @param  SearchCriteriaInterface $searchCriteria
     * @return ResourceModel\Comment\Collection
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        $collection = $this->collectionFactory->create();
        $collection->load();
        return $collection;
    }

    /**
     * @param  CommentInterface $comment
     * @return bool
     * @throws \Magento\Framework\Exception\StateException
     */
    public function delete(CommentInterface $comment)
    {
        $commentId = $comment->getId();
        try {
            $this->resourceModel->delete($comment);
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\StateException(
                __("Unable to remove comment with id %1", $commentId)
            );
        }
        unset($this->instancesById[$commentId]);
        return true;
    }

    /**
     * @param  int $commentId
     * @return bool
     * @throws \Magento\Framework\Exception\StateException
     */
    public function deleteById($commentId)
    {
        $comment = $this->getById($commentId);
        return $this->delete($comment);
    }
}
