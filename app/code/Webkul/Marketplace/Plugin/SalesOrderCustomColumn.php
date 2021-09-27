<?php
// namespace Webkul\Marketplace\Plugin;
 
// use Magento\Framework\Message\ManagerInterface as MessageManager;
// use Magento\Sales\Model\ResourceModel\Order\Grid\Collection as SalesOrderGridCollection;
 
// class SalesOrderCustomColumn
// {
//     private $messageManager;
//     private $collection;
 
//     public function __construct(MessageManager $messageManager,
//         SalesOrderGridCollection $collection
//     ) {
 
//         $this->messageManager = $messageManager;
//         $this->collection = $collection;
//     }
 
//     public function aroundGetReport(
//         \Magento\Framework\View\Element\UiComponent\DataProvider\CollectionFactory $subject,
//         \Closure $proceed,
//         $requestName
//     ) {
//         $result = $proceed($requestName);
//         if ($requestName == 'sales_order_grid_data_source') {
//             if ($result instanceof $this->collection
//             ) {
//                 $select = $this->collection->getSelect();
//                 $select->joinLeft(
//                     ["secondTable" => $this->collection->getTable("marketplace_orders")],
//                     'main_table.entity_id = secondTable.order_id',
//                     array('seller_id')
//                 );    
//                 // print_r($this->collection->getData());  exit();            
//                 return $this->collection;
//             }
//         }
//         return $result;
//     }
// }

?>