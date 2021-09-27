<?php
/**
 * Webkul Software.
 *
 *
 *
 * @category  Webkul
 * @package   Webkul_MobikulCore
 * @author    Webkul <support@webkul.com>
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html ASL Licence
 * @link      https://store.webkul.com/license.html
 */

namespace Webkul\MobikulCore\Model;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Webkul\MobikulCore\Api\Data\CategoryimagesInterface;
use Magento\Framework\Api\ExtensibleDataObjectConverter;

class CategoryimagesRepository implements \Webkul\MobikulCore\Api\CategoryimagesRepositoryInterface
{
    protected $_resourceModel;
    protected $_instances = [];
    protected $_collectionFactory;
    protected $_instancesById = [];
    protected $_categoryimagesFactory;
    protected $_extensibleDataObjectConverter;

    public function __construct(
        CategoryimagesFactory $categoryimagesFactory,
        ResourceModel\Categoryimages $resourceModel,
        ResourceModel\Categoryimages\CollectionFactory $collectionFactory,
        \Magento\Framework\Api\ExtensibleDataObjectConverter $extensibleDataObjectConverter
    ) {
        $this->_resourceModel = $resourceModel;
        $this->_collectionFactory = $collectionFactory;
        $this->_categoryimagesFactory = $categoryimagesFactory;
        $this->_extensibleDataObjectConverter = $extensibleDataObjectConverter;
    }

    public function save(CategoryimagesInterface $categoryimages)
    {
        $categoryimagesId = $categoryimages->getId();
        try {
            $this->_resourceModel->save($categoryimages);
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\CouldNotSaveException($e->getMessage());
        }
        unset($this->_instancesById[$categoryimages->getId()]);
        return $this->getById($categoryimages->getId());
    }

    public function getById($categoryimagesId)
    {
        $categoryimagesData = $this->_categoryimagesFactory->create();
        $categoryimagesData->load($categoryimagesId);
        $this->_instancesById[$categoryimagesId] = $categoryimagesData;
        return $this->_instancesById[$categoryimagesId];
    }

    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        $collection = $this->_collectionFactory->create();
        $collection->load();
        return $collection;
    }

    public function delete(CategoryimagesInterface $categoryimages)
    {
        $categoryimagesId = $categoryimages->getId();
        try {
            $this->_resourceModel->delete($categoryimages);
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\StateException(
                __("Unable to remove categoryimages record with id %1", $categoryimagesId)
            );
        }
        unset($this->_instancesById[$categoryimagesId]);
        return true;
    }

    public function deleteById($categoryimagesId)
    {
        $categoryimages = $this->getById($categoryimagesId);
        return $this->delete($categoryimages);
    }
}
