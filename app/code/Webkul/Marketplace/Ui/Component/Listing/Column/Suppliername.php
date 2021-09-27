<?php
namespace Webkul\Marketplace\Ui\Component\Listing\Column;
 
use \Magento\Sales\Api\OrderRepositoryInterface;
use \Magento\Framework\View\Element\UiComponent\ContextInterface;
use \Magento\Framework\View\Element\UiComponentFactory;
use \Magento\Ui\Component\Listing\Columns\Column;
use \Magento\Framework\Api\SearchCriteriaBuilder;

 
class Suppliername extends Column
{
 
    protected $_orderRepository;
    protected $_searchCriteria;
    protected $_customfactory;
 
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        // OrderRepositoryInterface $orderRepository,
        SearchCriteriaBuilder $criteria,
        \Webkul\Marketplace\Model\OrdersRepository $ordersRepository,
        \Webkul\Marketplace\Model\SellerFactory $sellerFactory,
        \Magento\Framework\App\ResourceConnection $resource,
        array $components = [], array $data = [])
    {
        $this->_searchCriteria  = $criteria;
        $this->_ordersRepository = $ordersRepository;
        $this->sellerFactory = $sellerFactory;
        $this->_resource = $resource;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }
 
    public function prepareDataSource(array $dataSource)
    {
        // echo "<pre>";
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $orders  = $this->_ordersRepository->getByOrderId($item["entity_id"]);
                // print_r($orders->getData()); 
                $suppliername = array();
                foreach ($orders as $key => $order) {
                    //echo $order->getSellerId(); 
                    $sellerdata = $this->sellerFactory->create()
                                ->getCollection()
                                ->addFieldToFilter(
                                    'seller_id',
                                    $order->getSellerId()
                                )->addFieldToFilter(
                                    'shop_title',
                                    ['notnull' => true]
                                )->getFirstItem(); 

                    // $suppliername[] = $sellerdata->getShopTitle();

                $connection = $this->_resource->getConnection(\Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION);
                $tblSalesOrder = $connection->getTableName('customer_grid_flat');
                $result1 = $connection->fetchRow('SELECT name FROM `'.$tblSalesOrder.'` WHERE entity_id='.$order->getSellerId()); 
                $suppliername[] = $result1['name'];
                }
                
                $item[$this->getData('name')] = implode(',', $suppliername);
            }
        }
        return $dataSource;
    }
}