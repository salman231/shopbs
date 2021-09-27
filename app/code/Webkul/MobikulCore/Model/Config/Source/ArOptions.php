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

namespace Webkul\MobikulCore\Model\Config\Source;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;

/**
 * Class ArOptions model
 */
class ArOptions extends AbstractSource
{
    /**
     * Get all options
     *
     * @return array
     */
    public function getAllOptions()
    {
        if (null === $this->_options) {
            $this->_options = [
                ["label" => __("2-D Model"), "value" => 0],
                ["label" => __("3-D Model"), "value" => 1]
            ];
        }
        return $this->_options;
    }

    /**
     * Get a text for option value
     *
     * @param string|integer $value values
     *
     * @return string|bool
     */
    public function getOptionText($value)
    {
        foreach ($this->getAllOptions() as $option) {
            if ($option["value"] == $value) {
                return $option["label"];
            }
        }
        return false;
    }
}
