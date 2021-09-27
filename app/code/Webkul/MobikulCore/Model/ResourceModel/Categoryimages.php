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

namespace Webkul\MobikulCore\Model\ResourceModel;

/**
 * Class Categoryimages resourceModel
 */
class Categoryimages extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{

    protected $store = null;

    protected function _construct()
    {
        $this->_init("mobikul_categoryimages", "id");
    }

    public function load(\Magento\Framework\Model\AbstractModel $object, $value, $field = null)
    {
        if (!is_numeric($value) && $field === null) {
            $field = "identifier";
        }
        return parent::load($object, $value, $field);
    }

    public function setStore($store)
    {
        $this->store = $store;
        return $this;
    }

    public function getStore()
    {
        return $this->_storeManager->getStore($this->store);
    }
}
