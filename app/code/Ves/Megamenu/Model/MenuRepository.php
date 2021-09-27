<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Ves\Megamenu\Model;

use Ves\Megamenu\Api\Data;
use Ves\Megamenu\Api\MenuRepositoryInterface;
use Ves\Megamenu\Model\ItemRepository;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Reflection\DataObjectProcessor;
use Ves\Megamenu\Model\ResourceModel\Menu as ResourceMenu;
use Ves\Megamenu\Model\ResourceModel\Menu\CollectionFactory as MenuCollectionFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\AuthorizationInterface;
use Magento\Authorization\Model\UserContextInterface;

/**
 * Class MenuRepositoryInterface
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class MenuRepository implements MenuRepositoryInterface
{
    /**
     * @var ResourceMenu
     */
    protected $resource;

    /**
     * @var MenuFactory
     */
    protected $menuFactory;

    /**
     * @var ItemFactory
     */
    protected $itemFactory;

    /**
     * @var MenuCollectionFactory
     */
    protected $menuCollectionFactory;

    /**
     * @var Data\MenuSearchResultsInterfaceFactory
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
     * @var \Ves\Megamenu\Api\Data\MenuInterfaceFactory
     */
    protected $dataMenuFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * @var UserContextInterface
     */
    private $userContext;

    /**
     * @var AuthorizationInterface
     */
    private $authorization;

    /**
     * @var ItemRepository
     */
    private $menuItemRepository;

    /**
     * @param ResourceMenu $resource
     * @param MenuFactory $menuFactory
     * @param Data\MenuInterfaceFactory $dataMenuFactory
     * @param MenuCollectionFactory $menuCollectionFactory
     * @param Data\MenuSearchResultsInterfaceFactory $searchResultsFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     * @param StoreManagerInterface $storeManager
     * @param CollectionProcessorInterface $collectionProcessor
     * @param ItemRepository $menuItemRepository
     * @param ItemFactory $itemFactory
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        ResourceMenu $resource,
        MenuFactory $menuFactory,
        Data\MenuInterfaceFactory $dataMenuFactory,
        MenuCollectionFactory $menuCollectionFactory,
        Data\MenuSearchResultsInterfaceFactory $searchResultsFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        StoreManagerInterface $storeManager,
        CollectionProcessorInterface $collectionProcessor = null,
        ItemRepository $menuItemRepository,
        ItemFactory $itemFactory
    ) {
        $this->resource = $resource;
        $this->menuFactory = $menuFactory;
        $this->menuItemRepository = $menuItemRepository;
        $this->menuCollectionFactory = $menuCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataMenuFactory = $dataMenuFactory;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->storeManager = $storeManager;
        $this->itemFactory = $itemFactory;
        $this->collectionProcessor = $collectionProcessor ?: $this->getCollectionProcessor();
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
     * @param \Ves\Megamenu\Api\Data\MenuInterface|Menu $menu
     * @return \Ves\Megamenu\Api\Data\MenuInterface
     * @throws CouldNotSaveException
     */
    public function save(\Ves\Megamenu\Api\Data\MenuInterface $menu)
    {
        if ($menu->getStoreId() === null) {
            $storeId = $this->storeManager->getStore()->getId();
            $menu->setStoreId($storeId);
        }
        try {
            //Validate changing of design.
            $userType = $this->getUserContext()->getUserType();
            if ((
                    $userType === UserContextInterface::USER_TYPE_ADMIN
                    || $userType === UserContextInterface::USER_TYPE_INTEGRATION
                )
                && $this->getAuthorization()->isAllowed('Ves_Megamenu::menu_save')
            ) {
                $design = $menu->getDesign();
                if(is_array($design)){
                    $design = serialize($design);
                    $menu->setDesign($design);
                }
                if (!$menu->getId()) {
                    //Do something
                } else {
                    $savedMenu = $this->getById($menu->getId());
                    if(!$menu->getAlias()){
                        $menu->setAlias ($savedMenu->getAlias());
                    }
                    $menu_structure = $menu->getStructure();
                    if(!$menu_structure || $menu_structure=='' || $menu_structure=='[]'){
                        $menu_structure = $savedMenu->getStructure();
                    }
                    $menu->setStructure($menu_structure);
                }
            }

            $this->resource->save($menu);
            if($menu->getId() && ($menu_items = $menu->getMenuItems())){
                //update menu items
                if(is_array($menu_items)){
                    foreach($menu_items as $_menu_item){
                        if($_menu_item && is_array($_menu_item)){
                            $_menu_item_model = $this->itemFactory->create();
                            $_menu_item_model->setData($_menu_item);
                            $this->menuItemRepository->save($_menu_item_model);
                        }
                    }
                }
            }
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(
                __('Could not save the menu: %1', $exception->getMessage()),
                $exception
            );
        }
        return $menu;
    }

    /**
     * Load Menu data by given Menu Identity
     *
     * @param string $menuId
     * @return Menu
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($menuId)
    {
        $menu = $this->menuFactory->create();
        $menu->load($menuId);
        if (!$menu->getId()) {
            throw new NoSuchEntityException(__('The Megamenu Profile with the "%1" ID doesn\'t exist.', $menuId));
        }
        return $menu;
    }

    /**
     * Load Menu data by given Menu Identity
     *
     * @param string $menuId
     * @param int $revision
     * @return Menu
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function revert($menuId, $revision)
    {
        if(!$revision || (int)$revision <= 0){
            throw new NoSuchEntityException(__('The Revision should be greater than 0.'));
        }
        $menu = $this->menuFactory->create();
        $menu->load($menuId);
        $menu->setRevertNext((int)$revision);
        if (!$menu->getId()) {
            throw new NoSuchEntityException(__('The Megamenu Profile with the "%1" ID doesn\'t exist.', $menuId));
        }else{
            $menu->save();
        }
        return $menu;
    }


    /**
     * Load Page data collection by given search criteria
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @param \Magento\Framework\Api\SearchCriteriaInterface $criteria
     * @return \Ves\Megamenu\Api\Data\MenuSearchResultsInterface
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $criteria)
    {
        /** @var \Ves\Megamenu\Model\ResourceModel\Menu\Collection $collection */
        $collection = $this->menuCollectionFactory->create();

        $this->collectionProcessor->process($criteria, $collection);

        /** @var Data\MenuSearchResultsInterface $searchResults */
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);
        $searchResults->setItems($collection->getItems());
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }

    /**
     * Delete Menu
     *
     * @param \Ves\Megamenu\Api\Data\MenuInterface $menu
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(\Ves\Megamenu\Api\Data\MenuInterface $menu)
    {
        try {
            $this->resource->delete($menu);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(
                __('Could not delete the megamenu profile: %1', $exception->getMessage())
            );
        }
        return true;
    }

    /**
     * Delete Menu by given Menu Identity
     *
     * @param string $menuId
     * @return bool
     * @throws CouldNotDeleteException
     * @throws NoSuchEntityException
     */
    public function deleteById($menuId)
    {
        return $this->delete($this->getById($menuId));
    }

    /**
     * Retrieve collection processor
     *
     * @deprecated 102.0.0
     * @return CollectionProcessorInterface
     */
    private function getCollectionProcessor()
    {
        if (!$this->collectionProcessor) {
            $this->collectionProcessor = \Magento\Framework\App\ObjectManager::getInstance()->get(
                'Ves\Megamenu\Model\Api\SearchCriteria\MenuCollectionProcessor'
            );
        }
        return $this->collectionProcessor;
    }
}
