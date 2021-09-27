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
use Webkul\MobikulCore\Api\Data\NotificationInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Api\ExtensibleDataObjectConverter;

class NotificationRepository implements \Webkul\MobikulCore\Api\NotificationRepositoryInterface
{
    protected $_notificationFactory;
    protected $_instances = [];
    protected $_instancesById = [];
    protected $_collectionFactory;
    protected $_resourceModel;
    protected $_extensibleDataObjectConverter;

    public function __construct(
        NotificationFactory $notificationFactory,
        ResourceModel\Notification $resourceModel,
        ResourceModel\Notification\CollectionFactory $collectionFactory,
        \Magento\Framework\Api\ExtensibleDataObjectConverter $extensibleDataObjectConverter
    ) {
        $this->_resourceModel = $resourceModel;
        $this->_collectionFactory = $collectionFactory;
        $this->_notificationFactory = $notificationFactory;
        $this->_extensibleDataObjectConverter = $extensibleDataObjectConverter;
    }

    public function save(NotificationInterface $notification)
    {
        $notificationId = $notification->getId();
        try {
            $this->_resourceModel->save($notification);
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\CouldNotSaveException($e->getMessage());
        }
        unset($this->_instancesById[$notification->getId()]);
        return $this->getById($notification->getId());
    }

    public function getById($notificationId)
    {
        $notificationData = $this->_notificationFactory->create();
        $notificationData->load($notificationId);
        $this->_instancesById[$notificationId] = $notificationData;
        return $this->_instancesById[$notificationId];
    }

    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        $collection = $this->_collectionFactory->create();
        $collection->load();
        return $collection;
    }

    public function delete(NotificationInterface $notification)
    {
        $notificationId = $notification->getId();
        try {
            $this->_resourceModel->delete($notification);
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\StateException(
                __("Unable to remove notification record with id %1", $notificationId)
            );
        }
        unset($this->_instancesById[$notificationId]);
        return true;
    }

    public function deleteById($notificationId)
    {
        $notification = $this->getById($notificationId);
        return $this->delete($notification);
    }
}
