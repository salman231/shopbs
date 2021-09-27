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

class Deliveryboy extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init("deliveryboy_deliveryboy", "id");
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
            $field = "id";
        }
        return parent::load($object, $value, $field);
    }
}
