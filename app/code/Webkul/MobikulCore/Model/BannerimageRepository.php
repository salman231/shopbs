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

use Webkul\MobikulCore\Api\Data\BannerimageInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Api\ExtensibleDataObjectConverter;

/**
 * Class BannerimageRepository model
 */
class BannerimageRepository implements \Webkul\MobikulCore\Api\BannerimageRepositoryInterface
{
    protected $_resourceModel;
    protected $_instances = [];
    protected $_collectionFactory;
    protected $_bannerimageFactory;
    protected $_instancesById = [];
    protected $_extensibleDataObjectConverter;

    public function __construct(
        BannerimageFactory $bannerimageFactory,
        ResourceModel\Bannerimage $resourceModel,
        ResourceModel\Bannerimage\CollectionFactory $collectionFactory,
        \Magento\Framework\Api\ExtensibleDataObjectConverter $extensibleDataObjectConverter
    ) {
        $this->_resourceModel = $resourceModel;
        $this->_collectionFactory = $collectionFactory;
        $this->_bannerimageFactory = $bannerimageFactory;
        $this->_extensibleDataObjectConverter = $extensibleDataObjectConverter;
    }

    public function save(BannerimageInterface $bannerimage)
    {
        $bannerimageId = $bannerimage->getId();
        try {
            $this->_resourceModel->save($bannerimage);
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\CouldNotSaveException($e->getMessage());
        }
        unset($this->_instancesById[$bannerimage->getId()]);
        return $this->getById($bannerimage->getId());
    }

    public function getById($bannerimageId)
    {
        $bannerimageData = $this->_bannerimageFactory->create();
        $bannerimageData->load($bannerimageId);
        $this->_instancesById[$bannerimageId] = $bannerimageData;
        return $this->_instancesById[$bannerimageId];
    }

    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        $collection = $this->_collectionFactory->create();
        $collection->load();
        return $collection;
    }

    public function delete(BannerimageInterface $bannerimage)
    {
        $bannerimageId = $bannerimage->getId();
        try {
            $this->_resourceModel->delete($bannerimage);
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\StateException(
                __("Unable to remove banner image with id %1", $bannerimageId)
            );
        }
        unset($this->_instancesById[$bannerimageId]);
        return true;
    }

    public function deleteById($bannerimageId)
    {
        $bannerimage = $this->getById($bannerimageId);
        return $this->delete($bannerimage);
    }
}
