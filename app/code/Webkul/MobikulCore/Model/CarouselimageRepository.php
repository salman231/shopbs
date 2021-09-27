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
use Webkul\MobikulCore\Api\Data\CarouselimageInterface;
use Magento\Framework\Api\ExtensibleDataObjectConverter;

/**
 * Class CarouselimageRepository model
 */
class CarouselimageRepository implements \Webkul\MobikulCore\Api\CarouselimageRepositoryInterface
{

    protected $_resourceModel;
    protected $_instances = [];
    protected $_collectionFactory;
    protected $_instancesById = [];
    protected $_carouselimageFactory;
    protected $_extensibleDataObjectConverter;

    public function __construct(
        CarouselimageFactory $carouselimageFactory,
        ResourceModel\Carouselimage $resourceModel,
        ResourceModel\Carouselimage\CollectionFactory $collectionFactory,
        \Magento\Framework\Api\ExtensibleDataObjectConverter $extensibleDataObjectConverter
    ) {
        $this->_resourceModel = $resourceModel;
        $this->_collectionFactory = $collectionFactory;
        $this->_carouselimageFactory = $carouselimageFactory;
        $this->_extensibleDataObjectConverter = $extensibleDataObjectConverter;
    }

    public function save(CarouselimageInterface $carouselimage)
    {
        $carouselimageId = $carouselimage->getId();
        try {
            $this->_resourceModel->save($carouselimage);
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\CouldNotSaveException($e->getMessage());
        }
        unset($this->_instancesById[$carouselimage->getId()]);
        return $this->getById($carouselimage->getId());
    }

    public function getById($carouselimageId)
    {
        $carouselimageData = $this->_carouselimageFactory->create();
        $carouselimageData->load($carouselimageId);
        $this->_instancesById[$carouselimageId] = $carouselimageData;
        return $this->_instancesById[$carouselimageId];
    }

    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        $collection = $this->_collectionFactory->create();
        $collection->load();
        return $collection;
    }

    public function delete(CarouselimageInterface $carouselimage)
    {
        $carouselimageId = $carouselimage->getId();
        try {
            $this->_resourceModel->delete($carouselimage);
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\StateException(
                __("Unable to remove carousel image with id %1", $carouselimageId)
            );
        }
        unset($this->_instancesById[$carouselimageId]);
        return true;
    }

    public function deleteById($carouselimageId)
    {
        $carouselimage = $this->getById($carouselimageId);
        return $this->delete($carouselimage);
    }
}
