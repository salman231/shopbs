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

namespace Webkul\MobikulCore\Ui\Component\Listing\Column;
 
use \Magento\Sales\Api\OrderRepositoryInterface;
use \Magento\Framework\View\Element\UiComponent\ContextInterface;
use \Magento\Framework\View\Element\UiComponentFactory;
use \Magento\Ui\Component\Listing\Columns\Column;
use \Magento\Framework\Api\SearchCriteriaBuilder;
use \Webkul\MobikulCore\Model\OrderPurchasePointFactory;
use \Webkul\MobikulCore\Helper\Data;
 
class OrderPlaceFrom extends Column
{
 
    protected $_orderRepository;
    protected $_searchCriteria;
    protected $_customfactory;
    protected $_helper;
 
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        OrderRepositoryInterface $orderRepository,
        SearchCriteriaBuilder $criteria,
        OrderPurchasePointFactory $customFactory,
        Data $helper,
        array $components = [],
        array $data = []
    ) {
        $this->_orderRepository = $orderRepository;
        $this->_searchCriteria  = $criteria;
        $this->_customfactory = $customFactory;
        $this->_helper = $helper;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }
 
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                try {
                    $order  = $this->_orderRepository->get($item["entity_id"]);
                    $order_id = $order->getEntityId();
                    $collection = $this->_customfactory->create()->getCollection();
                    $collection->addFieldToFilter('order_id', $order_id);
                    $data = $collection->getFirstItem();
                    $item[$this->getData('name')] = $data->getPurchasePoint();
                } catch (\Exception $e) {
                    $this->_helper->printLog($e->getMessage());
                }

            }
        }
        return $dataSource;
    }
}
