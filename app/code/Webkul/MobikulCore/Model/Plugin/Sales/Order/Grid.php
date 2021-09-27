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

namespace Webkul\MobikulCore\Model\Plugin\Sales\Order;
 
 /**
  * Grid Class for order purchase point.
  */
class Grid
{
 
    public static $table = 'sales_order_grid';
    public static $leftJoinTable = 'mobikul_orderPurchasePoint';
 
    public function afterSearch($intercepter, $collection)
    {
        if ($collection->getMainTable() === $collection->getConnection()->getTableName(self::$table)) {
 
            $leftJoinTableName = $collection->getConnection()->getTableName(self::$leftJoinTable);
 
            $collection
                ->getSelect()
                ->joinLeft(
                    ['co'=>$leftJoinTableName],
                    "co.order_id = main_table.entity_id",
                    [
                        'purchase_point' => 'co.purchase_point'
                    ]
                );
 
            $where = $collection->getSelect()->getPart(\Magento\Framework\DB\Select::WHERE);
 
            $collection->getSelect()->setPart(\Magento\Framework\DB\Select::WHERE, $where);
 
        }
        return $collection;
    }
}
