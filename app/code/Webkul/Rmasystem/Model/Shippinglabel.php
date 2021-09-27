<?php
namespace Webkul\Rmasystem\Model;

use Webkul\Rmasystem\Api\Data\ShippinglabelInterface;
use Magento\Framework\DataObject\IdentityInterface;

class Shippinglabel extends \Magento\Framework\Model\AbstractModel implements ShippinglabelInterface, IdentityInterface
{

    /**#@+
     * Post's Statuses
     */
    const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 0;
    /**#@-*/

    /**
     * CMS page cache tag
     */
    const CACHE_TAG = 'webkul_shippinglabel';

    /**
     * @var string
     */
    protected $_cacheTag = 'webkul_shippinglabel';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'webkul_shippinglabel';

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Webkul\Rmasystem\Model\ResourceModel\Shippinglabel::class);
    }

    /**
     * Prepare post's statuses.
     * Available event to customize statuses.
     *
     * @return array
     */
    public function getAvailableStatuses()
    {
        return [self::STATUS_ENABLED => __('Enabled'), self::STATUS_DISABLED => __('Disabled')];
    }
    /**
     * Return unique ID(s) for each object in system
     *
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

   /**
    * Get ID
    *
    * @return int|null
    */
    public function getId()
    {
        return $this->getData(self::ID);
    }
    /**
     * Get File Name
     *
     * @return string
     */
    public function getFilename()
    {
        return $this->getData(self::FILENAME);
    }
    /**
     * Get Title Name
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->getData(self::TITLE);
    }
    /**
     * Get Price
     *
     * @return string
     */
    public function getPrice()
    {
        return $this->getData(self::PRICE);
    }

    /**
     * Get Status
     *
     * @return boolen
     */
    public function getStatus()
    {
        return $this->getData(self::STATUS);
    }
    /**
     * Set ID
     *
     * @param int $id
     * @return \Webkul\Rmasystem\Api\Data\ReasonInterface
     */
    public function setId($id)
    {
        return $this->setData(self::ID, $id);
    }

    /**
     * Set File Name
     *
     * @return string
     */
    public function setFilename($filename)
    {
        return $this->setData(self::ID, $filename);
    }
    /**
     * Set Title Name
     *
     * @return string
     */
    public function setTitle($title)
    {
        return $this->setData(self::ID, $title);
    }
    /**
     * Set Price
     *
     * @return string
     */
    public function setPrice($price)
    {
        return $this->setData(self::ID, $price);
    }
    
     /**
      * Get Status
      *
      * @return boolen
      */
    public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
    }
}
