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
 * Class Carousel resourceModel
 */
class Carousel extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    protected function _construct()
    {
        $this->_init("mobikul_carousel", "id");
    }

    public function load(\Magento\Framework\Model\AbstractModel $object, $value, $field = null)
    {
        if (!is_numeric($value) && $field === null) {
            $field = "id";
        }
        return parent::load($object, $value, $field);
    }
}
