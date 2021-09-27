<?php
/**
 * Webkul Software.
 *
 * PHP version 7.0+
 *
 * @category  Webkul
 * @package   Webkul_DeliveryBoy
 * @author    Webkul <support@webkul.com>
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html ASL Licence
 * @link      https://store.webkul.com/license.html
 */
namespace Webkul\DeliveryBoy\Model\ResourceModel;

class Rating extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    const TABLE_NAME = 'deliveryboy_rating';
    const ENTITY_ID = 'id';

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init(self::TABLE_NAME, self::ENTITY_ID);
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel $object
     * @param mixed $value
     * @param string $field field to load by (defaults to model id)
     * @return self
     */
    public function load(\Magento\Framework\Model\AbstractModel $object, $value, $field = null)
    {
        if (!is_numeric($value) && $field === null) {
            $field = self::ENTITY_ID;
        }
        return parent::load($object, $value, $field);
    }
}
