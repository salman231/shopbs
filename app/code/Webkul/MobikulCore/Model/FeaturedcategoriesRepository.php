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
use Magento\Framework\Api\ExtensibleDataObjectConverter;
use Webkul\MobikulCore\Api\Data\FeaturedcategoriesInterface;

class FeaturedcategoriesRepository implements \Webkul\MobikulCore\Api\FeaturedcategoriesRepositoryInterface
{
    protected $_resourceModel;
    protected $_instances = [];
    protected $_collectionFactory;
    protected $_instancesById = [];
    protected $_featuredcategoriesFactory;
    protected $_extensibleDataObjectConverter;

    public function __construct(
        ResourceModel\Featuredcategories $resourceModel,
        FeaturedcategoriesFactory $featuredcategoriesFactory,
        ResourceModel\Featuredcategories\CollectionFactory $collectionFactory,
        \Magento\Framework\Api\ExtensibleDataObjectConverter $extensibleDataObjectConverter
    ) {
        $this->_resourceModel = $resourceModel;
        $this->_collectionFactory = $collectionFactory;
        $this->_featuredcategoriesFactory = $featuredcategoriesFactory;
        $this->_extensibleDataObjectConverter = $extensibleDataObjectConverter;
    }

    public function save(FeaturedcategoriesInterface $featuredcategories)
    {
        $featuredcategoriesId = $featuredcategories->getId();
        try {
            $this->_resourceModel->save($featuredcategories);
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\CouldNotSaveException($e->getMessage());
        }
        unset($this->_instancesById[$featuredcategories->getId()]);
        return $this->getById($featuredcategories->getId());
    }

    public function getById($featuredcategoriesId)
    {
        $featuredcategoriesData = $this->_featuredcategoriesFactory->create();
        $featuredcategoriesData->load($featuredcategoriesId);
        $this->_instancesById[$featuredcategoriesId] = $featuredcategoriesData;
        return $this->_instancesById[$featuredcategoriesId];
    }

    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        $collection = $this->_collectionFactory->create();
        $collection->load();
        return $collection;
    }

    public function delete(FeaturedcategoriesInterface $featuredcategories)
    {
        $featuredcategoriesId = $featuredcategories->getId();
        try {
            $this->_resourceModel->delete($featuredcategories);
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\StateException(
                __("Unable to remove featuredcategories record with id %1", $featuredcategoriesId)
            );
        }
        unset($this->_instancesById[$featuredcategoriesId]);
        return true;
    }

    public function deleteById($featuredcategoriesId)
    {
        $featuredcategories = $this->getById($featuredcategoriesId);
        return $this->delete($featuredcategories);
    }
}
