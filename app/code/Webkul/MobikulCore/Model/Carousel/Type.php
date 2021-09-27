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

namespace Webkul\MobikulCore\Model\Carousel;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class Type model
 */
class Type implements OptionSourceInterface
{
    public function toOptionArray()
    {
        return [
            ["label"=>__("Image Type"), "value"=>1],
            ["label"=>__("Product Type"), "value"=>2]
        ];
    }
}
