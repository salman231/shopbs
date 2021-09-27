<?php
namespace Webkul\Rmasystem\Model;

use Webkul\Rmasystem\Api\Data\CustomfieldInterface;
use Magento\Framework\DataObject\IdentityInterface;

class Customfield extends \Magento\Framework\Model\AbstractModel implements CustomfieldInterface, IdentityInterface
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
    const CACHE_TAG = 'webkul_customfield';

    /**
     * @var string
     */
    protected $_cacheTag = 'webkul_customfield';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'webkul_customfield';

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Webkul\Rmasystem\Model\ResourceModel\Customfield::class);
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
        return $this->getData(self::ENTITY_ID);
    }
    
    /**
     * SET ID
     *
     * @return int|null
     */
    public function setId($id)
    {
        return $this->setData(self::ENTITY_ID, $id);
    }
}
