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
namespace Webkul\MagentoChatSystem\Model\Agent;

use Webkul\MagentoChatSystem\Api\Data;
use Webkul\MagentoChatSystem\Api\AssignedChatRepositoryInterface;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Reflection\DataObjectProcessor;
use Webkul\MagentoChatSystem\Model;
use Webkul\MagentoChatSystem\Model\ResourceModel\AssignedChat as ResourceData;
use Webkul\MagentoChatSystem\Model\ResourceModel\AssignedChat\CollectionFactory as AssignedChatCollectionFactory;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class AgentDataRepository
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AssignedChatRepository implements AssignedChatRepositoryInterface
{
    /**
     * @var ResourceBlock
     */
    protected $resource;

    /**
     * @var BlockCollectionFactory
     */
    protected $assignedCollectionFactory;

    /**
     * @var DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @var DataObjectProcessor
     */
    protected $dataObjectProcessor;

    /**
     * @var \Webkul\MagentoChatSystem\Api\Data\MessageInterfaceFactory
     */
    protected $assignedDataFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param ResourceData $resource
     * @param Model\AssignedChatFactory $assignedDataFactory
     * @param AssignedChatCollectionFactory $assignedCollectionFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        ResourceData $resource,
        Model\AssignedChatFactory $assignedDataFactory,
        AssignedChatCollectionFactory $assignedCollectionFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        StoreManagerInterface $storeManager
    ) {
        $this->resource = $resource;
        $this->assignedDataFactory = $assignedDataFactory;
        $this->assignedCollectionFactory = $assignedCollectionFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->storeManager = $storeManager;
    }

    /**
     * Save Customer data
     *
     * @param \Webkul\MagentoChatSystem\Api\Data\AssignedChatInterface $assigned
     * @return \Webkul\MagentoChatSystem\Api\Data\AssignedChatInterface
     * @throws CouldNotSaveException
     */
    public function save(Data\AssignedChatInterface $assigned)
    {
        $storeId = $this->storeManager->getStore()->getId();
        $assigned->setStoreId($storeId);
        try {
            $this->resource->save($assigned);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        return $assigned;
    }

    /**
     * Load Preorder Complete data by given Block Identity
     *
     * @param string $id
     * @return \Webkul\MagentoChatSystem\Api\Data\AssignedChatInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($id)
    {
        $assigned = $this->assignedDataFactory->create();
        $this->resource->load($assigned, $id);
        if (!$assigned->getEntityId()) {
            throw new NoSuchEntityException(__('Agent with id "%1" does not exist.', $id));
        }
        return $assigned;
    }

    /**
     * Load Assigned Chat data by given customer id
     *
     * @param string $id
     * @return \Webkul\MagentoChatSystem\Api\Data\AssignedChatInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getByCustomerId($id)
    {
        $assigned = $this->assignedDataFactory->create();
        $assigned->load($id, 'customer_id');
        return $assigned;
    }

    /**
     * Delete PreorderComplete
     *
     * @param \Webkul\MagentoChatSystem\Api\Data\AssignedChatInterface $assigned
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(Data\AssignedChatInterface $assigned)
    {
        try {
            $this->resource->delete($assigned);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__($exception->getMessage()));
        }
        return true;
    }

    /**
     * Delete PreorderComplete by given Block Identity
     *
     * @param string $id
     * @return bool
     * @throws CouldNotDeleteException
     * @throws NoSuchEntityException
     */
    public function deleteById($id)
    {
        return $this->delete($this->getById($id));
    }
}
