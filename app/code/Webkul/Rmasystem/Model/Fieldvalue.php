<?php
namespace Webkul\Rmasystem\Model;

use Webkul\Rmasystem\Api\Data\FieldvalueInterface;
use Magento\Framework\DataObject\IdentityInterface;

class Fieldvalue extends \Magento\Framework\Model\AbstractModel implements FieldvalueInterface, IdentityInterface
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
    const CACHE_TAG = 'webkul_fieldvalue';

    /**
     * @var string
     */
    protected $_cacheTag = 'webkul_fieldvalue';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'webkul_fieldvalue';

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Webkul\Rmasystem\Model\ResourceModel\Fieldvalue::class);
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
