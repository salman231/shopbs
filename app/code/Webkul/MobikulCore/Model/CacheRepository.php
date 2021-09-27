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

use Webkul\MobikulCore\Api\Data\CacheInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class CacheRepository model
 */
class CacheRepository implements \Webkul\MobikulCore\Api\CacheRepositoryInterface
{
    protected $_resourceModel;
    protected $_instances = [];
    protected $_carouselFactory;
    protected $_collectionFactory;
    protected $_instancesById = [];

    public function __construct(
        CacheFactory $cacheFactory,
        ResourceModel\Cache $resourceModel,
        ResourceModel\Cache\CollectionFactory $collectionFactory
    ) {
        $this->_cacheFactory = $cacheFactory;
        $this->_resourceModel = $resourceModel;
        $this->_collectionFactory = $collectionFactory;
    }

    public function save(CacheInterface $cache)
    {
        $cacheId = $cache->getId();
        try {
            $this->_resourceModel->save($cache);
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\CouldNotSaveException($e->getMessage());
        }
        unset($this->_instancesById[$cache->getId()]);
        return $this->getById($cache->getId());
    }

    public function getById($cacheId)
    {
        $cacheData = $this->_cacheFactory->create();
        $cacheData->load($cacheId);
        $this->_instancesById[$cacheId] = $cacheData;
        return $this->_instancesById[$cacheId];
    }

    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        $collection = $this->_collectionFactory->create();
        $collection->load();
        return $collection;
    }

    public function delete(CacheInterface $cache)
    {
        $cacheId = $cache->getId();
        try {
            $this->_resourceModel->delete($cache);
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\StateException(__("Unable to remove cache with id %1", $cacheId));
        }
        unset($this->_instancesById[$cacheId]);
        return true;
    }

    public function deleteById($cacheId)
    {
        $cache = $this->getById($cacheId);
        return $this->delete($cache);
    }
}
