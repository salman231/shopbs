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
namespace Webkul\DeliveryBoy\Controller\Order;

use Magento\Framework\App\Action;

class GetTrackingData extends Action\Action
{
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var \Magento\Framework\Filesystem\Driver\File
     */
    protected $fileDriver;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @param Action\Context $context
     * @param \Magento\Sales\Model\OrderFactory $order
     * @param \Magento\Customer\Model\Address\Config $addressConfig
     * @param \Webkul\DeliveryBoy\Helper\Data $helper
     * @param \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator
     * @param \Webkul\DeliveryBoy\Model\DeliveryboyFactory $deliveryBoyFactory
     * @param \Webkul\DeliveryBoy\Model\OrderFactory $deliveryBoyOrderFactory
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Framework\Filesystem\Driver\File $fileDriver
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        Action\Context $context,
        \Magento\Sales\Model\OrderFactory $order,
        \Magento\Customer\Model\Address\Config $addressConfig,
        \Webkul\DeliveryBoy\Helper\Data $helper,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        \Webkul\DeliveryBoy\Model\DeliveryboyFactory $deliveryBoyFactory,
        \Webkul\DeliveryBoy\Model\OrderFactory $deliveryBoyOrderFactory,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\Filesystem\Driver\File $fileDriver,
        \Psr\Log\LoggerInterface $logger
    ) {
        parent::__construct($context);

        $this->helper = $helper;
        $this->orderFactory = $order;
        $this->jsonHelper = $jsonHelper;
        $this->addressConfig = $addressConfig;
        $this->formKeyValidator = $formKeyValidator;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->deliveryBoyOrderFactory = $deliveryBoyOrderFactory;
        $this->deliveryBoyFactory = $deliveryBoyFactory;
        $this->fileDriver = $fileDriver;
        $this->logger = $logger;

        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $result = $this->resultJsonFactory->create();
        $resultData = [];
        $resultData['success'] = true;
        $resultData['message'] = "";
        if ($this->formKeyValidator->validate($this->getRequest())) {
            $googleMapApiKey = $this->helper->getGoogleMapKey();
            $deliveryboyOrderId = $this->getRequest()->getParam('deliveryboy_order_id');
            $deliveryBoyOrderDetails = $this->deliveryBoyOrderFactory
                ->create()
                ->getCollection()
                ->addFieldToFilter('id', $deliveryboyOrderId)
                ->getFirstItem();
            $orderId = $deliveryBoyOrderDetails->getOrderId();
            $order = $this->orderFactory->create()->load($orderId);
            $shippingAddress = $order->getShippingAddress();
            $renderer = $this->addressConfig->getFormatByCode('html')->getRenderer();
            $shippingAdd = strip_tags($renderer->renderArray($shippingAddress));
            $prepAddr = str_replace(' ', '+', $shippingAdd);
            try {
                $geocode = $this->fileDriver->fileGetContents(
                    'https://maps.google.com/maps/api/geocode/json?key=' . $googleMapApiKey .
                    '&address=' . $prepAddr . '&sensor=false'
                );
            } catch (\Throwable $e) {
                $this->logger->debug($e-getMessage());
                $geocode = "";
            }
            $output= json_decode($geocode);
            if (isset($output->results[0])) {
                $resultData['customer_lat'] = $output->results[0]->geometry->location->lat;
                $resultData['customer_lng'] = $output->results[0]->geometry->location->lng;
            } else {
                $resultData['success'] = false;
                $errorMessage = trim($output->error_message ?? "");
                $resultData['message'] = $errorMessage ? __($errorMessage) : __("Invalid Customer Address");
            }
            $deliveryBoy = $this->deliveryBoyFactory->create()->load($deliveryBoyOrderDetails['deliveryboy_id']);
            $resultData['db_lat'] = $deliveryBoy->getLatitude();
            $resultData['db_lng'] = $deliveryBoy->getLongitude();
            $result = $result->setData($resultData);
        } else {
            $result = $result->setData(['success' => false,'message'=>__('Invalid Request')]);
        }
        
        return $result;
    }
}
