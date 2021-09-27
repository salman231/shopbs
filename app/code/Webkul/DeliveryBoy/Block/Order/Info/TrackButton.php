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
namespace Webkul\DeliveryBoy\Block\Order\Info;

use Webkul\DeliveryBoy\Api\Data\OrderInterface;
use Webkul\DeliveryBoy\Helper\Data as DeliveryBoyDataHelper;

class TrackButton extends \Magento\Framework\View\Element\Template
{
    /**
     * @var array
     */
    protected $deliveryBoyRatings;

    /**
     * @var string
     */
    protected $_template = 'Webkul_DeliveryBoy::order/info/trackbutton.phtml';

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Webkul\DeliveryBoy\Model\ResourceModel\Order\CollectionFactory
     */
    protected $deliveryBoyOrderCollectionF;

    /**
     * @var \Webkul\DeliveryBoy\Model\DeliveryboyFactory
     */
    protected $deliveryBoyF;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var \Magento\Framework\Session\SessionManagerInterface
     */
    protected $session;

    /**
     * @var \Webkul\DeliveryBoy\Model\ResourceModel\Rating\CollectionFactory
     */
    protected $deliveryBoyRatingCollectionF;

    /**
     * @var DeliveryBoyDataHelper
     */
    protected $deliveryBoyDataHelper;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Webkul\DeliveryBoy\Model\ResourceModel\Order\CollectionFactory $deliveryBoyOrderCollectionF
     * @param \Webkul\DeliveryBoy\Model\DeliveryboyFactory $deliveryBoyF
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param \Magento\Framework\Session\SessionManagerInterface $session
     * @param \Webkul\DeliveryBoy\Model\ResourceModel\Rating\CollectionFactory $deliveryBoyRatingCollectionF
     * @param DeliveryBoyDataHelper $deliveryBoyDataHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Webkul\DeliveryBoy\Model\ResourceModel\Order\CollectionFactory $deliveryBoyOrderCollectionF,
        \Webkul\DeliveryBoy\Model\DeliveryboyFactory $deliveryBoyF,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Framework\Session\SessionManagerInterface $session,
        \Webkul\DeliveryBoy\Model\ResourceModel\Rating\CollectionFactory $deliveryBoyRatingCollectionF,
        DeliveryBoyDataHelper $deliveryBoyDataHelper,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        $this->deliveryBoyOrderCollectionF = $deliveryBoyOrderCollectionF;
        $this->deliveryBoyF = $deliveryBoyF;
        $this->urlBuilder = $urlBuilder;
        $this->session = $session;
        $this->deliveryBoyRatingCollectionF = $deliveryBoyRatingCollectionF;
        $this->deliveryBoyDataHelper = $deliveryBoyDataHelper;
        parent::__construct($context, $data);
        $this->_isScopePrivate = true;
    }

    /**
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        return $this->_coreRegistry->registry('current_order');
    }

    /**
     * @return \Webkul\DeliveryBoy\Api\Data\OrderInterface
     */
    public function getDeliveryBoyOrder()
    {
        $order = $this->getOrder();
        $orderIncrementId = $order->getIncrementId();
        $deliveryboyOrderCollection = $this->deliveryBoyOrderCollectionF->create()
            ->addFieldToSelect('*')
            ->addFieldToFilter(OrderInterface::INCREMENT_ID, $orderIncrementId)
            ->addFieldToFilter(OrderInterface::ORDER_STATUS, \Magento\Sales\Model\Order::STATE_PROCESSING)
            ->addFieldToFilter(OrderInterface::ID, $this->getDeliveryboyOrderId());
        $deliveryboyOrder = $deliveryboyOrderCollection->getFirstItem();
        
        return $deliveryboyOrder;
    }

    /**
     * @return \Webkul\DeliveryBoy\Api\Data\DeliveryboyInterface
     */
    public function getDeliveryBoy()
    {
        $deliveryBoyId = $this->getDeliveryBoyOrder()->getDeliveryboyId();
        $deliveryboy = $this->deliveryBoyF->create()->load($deliveryBoyId);

        return $deliveryboy;
    }

    /**
     * @return bool
     */
    public function canTrackOrder()
    {
        return ($this->getDeliveryBoyOrder()->getId() != null);
    }

    /**
     * @return string
     */
    public function getTrackingDataAction()
    {
        return $this->urlBuilder->getUrl('expressdelivery/order/getTrackingData');
    }

    /**
     * @return string
     */
    public function getBaseUrl()
    {
        return $this->urlModel->getStore()->getBaseUrl();
    }

    /**
     * @return string
     */
    public function getGoogleMapKey()
    {
        return $this->deliveryBoyDataHelper->getGoogleMapKey();
    }
}
