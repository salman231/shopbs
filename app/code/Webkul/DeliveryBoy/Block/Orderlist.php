<?php
/**
 * Webkul Software.
 *
 * PHP version 7.0+
 *
 * @category  Webkul
 * @package   Webkul_DeliveryBoy
 * @author    Webkul <support@webkul.com>
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html ASL Licence
 * @link      https://store.webkul.com/license.html
 */
namespace Webkul\DeliveryBoy\Block;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\View\Element\Template;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactoryInterface;

class Orderlist extends Template
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Sales\Model\Order\Config
     */
    protected $_orderConfig;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Collection
     */
    protected $orders;

    /**
     * @var CollectionFactoryInterface
     */
    private $orderCollectionFactory;

    /**
     * @var \Webkul\DeliveryBoy\Helper\Data
     */
    protected $helperData;

    /**
     * @param \Webkul\DeliveryBoy\Helper\Data $helper
     * @param \Magento\Sales\Model\Order\Config $orderConfig
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Webkul\DeliveryBoy\Helper\Data $helper,
        \Magento\Sales\Model\Order\Config $orderConfig,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = []
    ) {
        $this->helperData = $helperData;
        $this->_customerSession = $customerSession;
        $this->_orderConfig = $orderConfig;
        parent::__construct($context, $data);
    }

    /**
     * @return CollectionFactoryInterface
     *
     * @deprecated 100.1.1
     */
    private function getOrderCollectionFactory()
    {
        if ($this->orderCollectionFactory === null) {
            $this->orderCollectionFactory = ObjectManager::getInstance()->get(CollectionFactoryInterface::class);
        }
        return $this->orderCollectionFactory;
    }

    /**
     * @return bool|\Magento\Sales\Model\ResourceModel\Order\Collection
     */
    public function getOrders()
    {
        if (!($customerId = $this->_customerSession->getCustomerId())) {
            return false;
        }
        if (!$this->orders) {
            $allowedShipping = explode(",", $this->helperData->getAllowedShipping());
            $this->orders = $this->getOrderCollectionFactory()->create($customerId)
                ->addFieldToSelect("*")
                ->addFieldToFilter("status", ["in"=>$this->_orderConfig->getVisibleOnFrontStatuses()])
                ->addFieldToFilter("shipping_method", ["in"=>$allowedShipping])
                ->setOrder("created_at", "desc");
        }
        return $this->orders;
    }

    /**
     * @return self
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->getOrders()) {
            $pager = $this->getLayout()
                ->createBlock(\Magento\Theme\Block\Html\Pager::class, "sales.order.history.pager")
                ->setCollection($this->getOrders());
            $this->setChild("pager", $pager);
            $this->getOrders()->load();
        }
        return $this;
    }

    /**
     * @return string
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml("pager");
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @return string
     */
    public function getViewUrl($order)
    {
        return $this->getUrl("sales/order/view", ["order_id" => $order->getId()]);
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @return string
     */
    public function getTrackUrl($order)
    {
        return $this->getUrl("expressdelivery/order/track", ["order_id" => $order->getId()]);
    }

    /**
     * @return string
     */
    public function getBackUrl()
    {
        return $this->getUrl("customer/account/");
    }
}
