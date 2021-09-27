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

namespace Webkul\MobikulCore\Model\System;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class Downloadlinktheme model
 */
class Downloadlinktheme implements OptionSourceInterface
{
    public function toOptionArray()
    {
        $options = [];
        array_push($options, ["value"=>"mk-lightTheme", "label"=>"Light Theme"]);
        array_push($options, ["value"=>"mk-darkTheme", "label"=>"Dark Theme"]);
        return $options;
    }
}
