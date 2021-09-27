<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Ves\Megamenu\Model;

use Ves\Megamenu\Api\Data;
use Ves\Megamenu\Api\ItemRepositoryInterface;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Reflection\DataObjectProcessor;
use Ves\Megamenu\Model\ResourceModel\Item as ResourceItem;
use Ves\Megamenu\Model\ResourceModel\Item\CollectionFactory as ItemCollectionFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\AuthorizationInterface;
use Magento\Authorization\Model\UserContextInterface;

/**
 * Class ItemRepositoryInterface
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ItemRepository implements ItemRepositoryInterface
{
    /**
     * @var ResourceItem
     */
    protected $resource;

    /**
     * @var ItemFactory
     */
    protected $ItemFactory;

    /**
     * @var ItemCollectionFactory
     */
    protected $itemCollectionFactory;

    /**
     * @var Data\ItemSearchResultsInterfaceFactory
     */
    protected $searchResultsFactory;

    /**
     * @var DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @var DataObjectProcessor
     */
    protected $dataObjectProcessor;

    /**
     * @var \Ves\Megamenu\Api\Data\ItemInterfaceFactory
     */
    protected $dataItemFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var UserContextInterface
     */
    private $userContext;

    /**
     * @var AuthorizationInterface
     */
    private $authorization;

    /**
     * @param ResourceItem $resource
     * @param ItemFactory $itemFactory
     * @param Data\ItemInterfaceFactory $dataItemFactory
     * @param ItemCollectionFactory $itemCollectionFactory
     * @param Data\ItemSearchResultsInterfaceFactory $searchResultsFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     * @param StoreManagerInterface $storeManager
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        ResourceItem $resource,
        ItemFactory $itemFactory,
        Data\ItemInterfaceFactory $dataItemFactory,
        ItemCollectionFactory $itemCollectionFactory,
        Data\ItemSearchResultsInterfaceFactory $searchResultsFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        StoreManagerInterface $storeManager
    ) {
        $this->resource = $resource;
        $this->itemFactory = $itemFactory;
        $this->itemCollectionFactory = $itemCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataItemFactory = $dataItemFactory;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->storeManager = $storeManager;
    }

    /**
     * Get user context.
     *
     * @return UserContextInterface
     */
    private function getUserContext(): UserContextInterface
    {
        if (!$this->userContext) {
            $this->userContext = ObjectManager::getInstance()->get(UserContextInterface::class);
        }

        return $this->userContext;
    }

    /**
     * Get authorization service.
     *
     * @return AuthorizationInterface
     */
    private function getAuthorization(): AuthorizationInterface
    {
        if (!$this->authorization) {
            $this->authorization = ObjectManager::getInstance()->get(AuthorizationInterface::class);
        }

        return $this->authorization;
    }

    /**
     * Save Menu data
     *
     * @param \Ves\Megamenu\Api\Data\ItemInterface|Item $menuItem
     * @return \Ves\Megamenu\Api\Data\ItemInterface
     * @throws CouldNotSaveException
     */
    public function save(\Ves\Megamenu\Api\Data\ItemInterface $menuItem)
    {
        try {
            //Validate changing of menu item.
            $userType = $this->getUserContext()->getUserType();
            if ((
                    $userType === UserContextInterface::USER_TYPE_ADMIN
                    || $userType === UserContextInterface::USER_TYPE_INTEGRATION
                )
                && $this->getAuthorization()->isAllowed('Ves_Megamenu::menu_save')
            ) {
                if (!$menuItem->getId()) {
                    if(!$menuItem->getMenuId()){
                        throw new CouldNotSaveException(
                            __('Could not save the menu Item because missing menu_id')
                        );
                        return $menuItem;
                    }
                } else {
                    $savedMenuItem = $this->getById($menuItem->getId());
                    $menu_id = $savedMenuItem->getMenuId();
                    $menuItem->setMenuId((int)$menu_id);
                }
            }

            $this->resource->save($menuItem);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(
                __('Could not save the menu Item: %1', $exception->getMessage()),
                $exception
            );
        }
        return $menuItem;
    }

    /**
     * {@inheritdoc}
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $criteria)
    {
        /** @var \Ves\Megamenu\Model\ResourceModel\Item\Collection $collection */
        $collection = $this->itemCollectionFactory->create();
        foreach ($criteria->getFilterGroups() as $filterGroup) {
            foreach ($filterGroup->getFilters() as $filter) {
                $condition = $filter->getConditionType() ?: 'eq';
                $collection->addFieldToFilter($filter->getField(), [$condition => $filter->getValue()]);
            }
        }
        
        $sortOrders = $criteria->getSortOrders();
        if ($sortOrders) {
            /** @var SortOrder $sortOrder */
            foreach ($sortOrders as $sortOrder) {
                $collection->addOrder(
                    $sortOrder->getField(),
                    ($sortOrder->getDirection() == SortOrder::SORT_ASC) ? 'ASC' : 'DESC'
                );
            }
        }
        $collection->setCurPage($criteria->getCurrentPage());
        $collection->setPageSize($criteria->getPageSize());
        
        /** @var Data\ItemSearchResultsInterface $searchResults */
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);
        $searchResults->setTotalCount($collection->getSize());
        $searchResults->setItems($collection->getItems());
        return $searchResults;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(\Ves\Megamenu\Api\Data\ItemInterface $menu_item)
    {
        try {
            $this->resource->delete($menu_item);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(
                __('Could not delete the menu Item: %1', $exception->getMessage())
            );
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($menuItemId)
    {
        return $this->delete($this->getById($menuItemId));
    }

    public function getMenuItemById($menuItemId, $storeId, $isBackend = false){
        $menuItem = $this->itemFactory->create();
        $menuItem->load($menuItemId);
        if (!$menuItem->getId()) {
            throw new NoSuchEntityException(__('The Megamenu Item with the "%1" ID doesn\'t exist.', $menuItemId));
        }
        $menuItem->renderHtmlShortcode($storeId, $isBackend);
        return $menuItem;
    }
    public function getMenuItemsByMenuId($menuId, $storeId, $isBackend = false)
    {
        /** @var \Ves\Megamenu\Model\ResourceModel\Item\Collection $collection */
        $collection = $this->itemCollectionFactory->create();
        $collection->addFieldToFilter("menu_id", (int)$menuId);
        $collection->addOrder("id","DESC");
        $collection->generateMenuItems($storeId, $isBackend);
        
        /** @var Data\ItemSearchResultsInterface $searchResults */
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setTotalCount($collection->getSize());
        $searchResults->setItems($collection->getItems());
        return $searchResults;
    }
    /**
     * {@inheritdoc}
     */
    public function getById($menuItemId, $storeId)
    {
        $menuItem = $this->getMenuItemById($menuItemId, $storeId, false);
        return $menuItem;
    }
    /**
     * {@inheritdoc}
     */
    public function getByMenuId($menuId, $storeId)
    {
        /** @var Data\ItemSearchResultsInterface $searchResults */
        $searchResults = $this->getMenuItemsByMenuId($menuId, $storeId, false);
        return $searchResults;
    }
    /**
     * {@inheritdoc}
     */
    public function getByIdBackend($menuItemId, $storeId){
        $menuItem = $this->getMenuItemById($menuItemId, $storeId, true);
        return $menuItem;
    }
    /**
     * {@inheritdoc}
     */
    public function getByMenuIdBackend($menuId, $storeId)
    {
        /** @var Data\ItemSearchResultsInterface $searchResults */
        $searchResults = $this->getMenuItemsByMenuId($menuId, $storeId, true);
        return $searchResults;
    }
}
