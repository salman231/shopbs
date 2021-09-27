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
namespace Webkul\DeliveryBoy\Observer;

use Psr\Log\LoggerInterface;

class SalesOrderPlaceAfterObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Magento\Store\Model\App\Emulation
     */
    protected $emulate;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;

    /**
     * @var \Webkul\DeliveryBoy\Model\TokenFactory
     */
    protected $deviceTokenFactory;

    /**
     * @var \Webkul\DeliveryBoy\Model\OrderFactory
     */
    protected $deliveryboyOrderFactory;

    /**
     * @var \Webkul\DeliveryBoy\Helper\Operation
     */
    private $operationHelper;

    /**
     * @param \Magento\Store\Model\App\Emulation $emulate
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Webkul\DeliveryBoy\Helper\Data $deliveryboyHelper
     * @param \Webkul\DeliveryBoy\Model\TokenFactory $deviceTokenFactory
     * @param \Webkul\DeliveryBoy\Model\OrderFactory $deliveryboyOrderFactory
     * @param \Webkul\DeliveryBoy\Helper\Operation $operationHelper
     * @param LoggerInterface $logger
     */
    public function __construct(
        \Magento\Store\Model\App\Emulation $emulate,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Webkul\DeliveryBoy\Helper\Data $deliveryboyHelper,
        \Webkul\DeliveryBoy\Model\TokenFactory $deviceTokenFactory,
        \Webkul\DeliveryBoy\Model\OrderFactory $deliveryboyOrderFactory,
        \Webkul\DeliveryBoy\Helper\Operation $operationHelper,
        LoggerInterface $logger
    ) {
        $this->emulate = $emulate;
        $this->jsonHelper = $jsonHelper;
        $this->deviceTokenFactory = $deviceTokenFactory;
        $this->deliveryboyOrderFactory = $deliveryboyOrderFactory;
        $this->deliveryboyHelper = $deliveryboyHelper;
        $this->operationHelper = $operationHelper;
        $this->logger = $logger;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try {
            $order = $observer->getOrder();
            $allowedShipping = explode(",", $this->deliveryboyHelper->getAllowedShipping());
            if (in_array($order->getShippingMethod(), $allowedShipping)) {
                $environment = $this->emulate->startEnvironmentEmulation(
                    $order->getStoreId(),
                    \Magento\Framework\App\Area::AREA_ADMINHTML,
                    true
                );
                $this->deliveryboyOrderFactory->create()
                    ->setAssignStatus(0)
                    ->setOrderId($order->getId())
                    ->setOrderStatus($order->getState())
                    ->setDeliveryboyId(0)
                    ->setIncrementId($order->getIncrementId())
                    ->save();
                $authKey = $this->deliveryboyHelper->getFcmApiKey();
                if (empty($authKey)) {
                    return ;
                }
                $headers = [
                    "Authorization: key=" . $authKey,
                    "Content-Type: application/json",
                ];
                $message = [
                    "id" => "deliveryBoyNewOrder-" . $order->getId(),
                    "body" => __("Please check the order status and assign to delivery boy."),
                    "title" => __("New Order #%1", $order->getIncrementId()),
                    "sound" => "default",
                    "message" => __("Please check the order status and assign to delivery boy."),
                    "incrementId" => $order->getIncrementId(),
                    "notificationType" => "orderStatus"
                ];
                $fields = [
                    "data" => $message,
                    "priority" => "high",
                    "time_to_live" => 30,
                    "delay_while_idle" => true,
                    "content_available" => true
                ];
                $tokenCollection = $this->deviceTokenFactory->create()
                    ->getCollection()->addFieldToFilter("is_admin", 1);
                foreach ($tokenCollection as $eachToken) {
                    $fields['to'] = $eachToken->getToken();
                    if ($eachToken->getOs() == "ios") {
                        $fields["notification"] = $message;
                    }
                    $result = $this->operationHelper->send($headers, $fields);
                    if (isset($result["success"], $result["failure"])) {
                        if ($result["success"] == 0 && $result["failure"] == 1) {
                            $eachToken->delete();
                        }
                    }
                }
                $this->emulate->stopEnvironmentEmulation($environment);
            }
        } catch (\Throwable $e) {
            $this->logger->debug($e->getMessage());
        }
    }
}
