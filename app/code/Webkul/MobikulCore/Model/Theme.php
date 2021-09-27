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

namespace Webkul\MobikulCore\Model;

/**
 * Class Theme model
 */
class Theme implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        $data = [];
        $data[] = ["value"=>1, "label"=>"red-green"];
        $data[] = ["value"=>2, "label"=>"light green"];
        $data[] = ["value"=>3, "label"=>"deep purple-pink"];
        $data[] = ["value"=>4, "label"=>"blue-orange"];
        $data[] = ["value"=>5, "label"=>"light blue-red"];
        return  $data;
    }
}
