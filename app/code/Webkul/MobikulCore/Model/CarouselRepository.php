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

use Webkul\MobikulCore\Api\Data\CarouselInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Api\ExtensibleDataObjectConverter;

/**
 * Class CarouselRepository model
 */
class CarouselRepository implements \Webkul\MobikulCore\Api\CarouselRepositoryInterface
{
    protected $_resourceModel;
    protected $_instances = [];
    protected $_carouselFactory;
    protected $_collectionFactory;
    protected $_instancesById = [];
    protected $_extensibleDataObjectConverter;

    public function __construct(
        CarouselFactory $carouselFactory,
        ResourceModel\Carousel $resourceModel,
        ResourceModel\Carousel\CollectionFactory $collectionFactory,
        \Magento\Framework\Api\ExtensibleDataObjectConverter $extensibleDataObjectConverter
    ) {
        $this->_resourceModel = $resourceModel;
        $this->_carouselFactory = $carouselFactory;
        $this->_collectionFactory = $collectionFactory;
        $this->_extensibleDataObjectConverter = $extensibleDataObjectConverter;
    }

    public function save(CarouselInterface $carousel)
    {
        $carouselId = $carousel->getId();
        try {
            $this->_resourceModel->save($carousel);
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\CouldNotSaveException($e->getMessage());
        }
        unset($this->_instancesById[$carousel->getId()]);
        return $this->getById($carousel->getId());
    }

    public function getById($carouselId)
    {
        $carouselData = $this->_carouselFactory->create();
        $carouselData->load($carouselId);
        $this->_instancesById[$carouselId] = $carouselData;
        return $this->_instancesById[$carouselId];
    }

    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        $collection = $this->_collectionFactory->create();
        $collection->load();
        return $collection;
    }

    public function delete(CarouselInterface $carousel)
    {
        $carouselId = $carousel->getId();
        try {
            $this->_resourceModel->delete($carousel);
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\StateException(
                __("Unable to remove carousel with id %1", $carouselId)
            );
        }
        unset($this->_instancesById[$carouselId]);
        return true;
    }

    public function deleteById($carouselId)
    {
        $carousel = $this->getById($carouselId);
        return $this->delete($carousel);
    }
}
