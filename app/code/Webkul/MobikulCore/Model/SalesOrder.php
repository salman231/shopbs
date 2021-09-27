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

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\DataObject\IdentityInterface;
use Webkul\MobikulCore\Api\Data\SalesOrderInterface;

/**
 * Class SalesOrder model
 */
class SalesOrder extends AbstractModel implements SalesOrderInterface, IdentityInterface
{
    const NOROUTE_ID = "no-route";
    const CACHE_TAG = "mobikul_sales_order";
    protected $_cacheTag = "mobikul_sales_order";
    protected $_eventPrefix = "mobikul_sales_order";

    protected function _construct()
    {
        $this->_init(\Webkul\MobikulCore\Model\ResourceModel\SalesOrder::class);
    }

    public function load($id, $field = null)
    {
        if ($id === null) {
            return $this->noRouteBannerimage();
        }
        return parent::load($id, $field);
    }

    public function noRouteBannerimage()
    {
        return $this->load(self::NOROUTE_ID, $this->getIdFieldName());
    }

    public function getIdentities()
    {
        return [self::CACHE_TAG . "_" . $this->getId()];
    }

    public function getId()
    {
        return parent::getData(self::ID);
    }

    public function setId($id)
    {
        return $this->setData(self::ID, $id);
    }

    public function getOrderId()
    {
        return parent::getData(self::ORDER_ID);
    }

    public function setOrderId($orderId)
    {
        return $this->setData(self::ORDER_ID, $orderId);
    }

    public function getStoreId()
    {
        return parent::getData(self::STORE_ID);
    }

    public function setStoreId($storeId)
    {
        return $this->setData(self::STORE_ID, $storeId);
    }

    public function getCreatedAt()
    {
        return parent::getData(self::CREATED_AT);
    }

    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

    public function getCustomerId()
    {
        return parent::getData(self::CUSTOMER_ID);
    }

    public function setCustomerId($customerId)
    {
        return $this->setData(self::CUSTOMER_ID, $customerId);
    }

    public function getOrderTotal()
    {
        return parent::getData(self::ORDER_TOTAL);
    }

    public function setOrderTotal($orderTotal)
    {
        return $this->setData(self::ORDER_TOTAL, $orderTotal);
    }

    public function getRealOrderId()
    {
        return parent::getData(self::REAL_ORDER_ID);
    }

    public function setRealOrderId($realOrderId)
    {
        return $this->setData(self::REAL_ORDER_ID, $realOrderId);
    }

    public function getCustomerName()
    {
        return parent::getData(self::CUSTOMER_NAME);
    }

    public function setCustomerName($customerName)
    {
        return $this->setData(self::CUSTOMER_NAME, $customerName);
    }
}
