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

namespace Webkul\MobikulCore\Observer;

use Webkul\MobikulCore\Model\ConstantRepo;

/**
 * OrderStatusNotification Class observer
 */
class OrderStatusNotification implements \Magento\Framework\Event\ObserverInterface
{
    protected $helper;
    protected $emulate;
    protected $session;
    protected $jsonHelper;
    protected $deviceToken;

    /**
     * Construct Function for class OrderStatusNotification
     *
     * @param \Webkul\MobikulCore\Helper\Data                    $helper      helper
     * @param \Magento\Store\Model\App\Emulation                 $emulate     emulate
     * @param \Magento\Framework\Json\Helper\Data                $jsonHelper  jsonHelper
     * @param \Webkul\MobikulCore\Model\DeviceToken              $deviceToken deviceToken
     * @param \Magento\Framework\Session\SessionManagerInterface $session     session
     *
     * @return void
     */
    public function __construct(
        \Webkul\MobikulCore\Helper\Data $helper,
        \Magento\Store\Model\App\Emulation $emulate,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Webkul\MobikulCore\Model\DeviceToken $deviceToken,
        \Magento\Framework\Session\SessionManagerInterface $session,
        \Magento\Framework\HTTP\Client\Curl $curl
    ) {
        $this->helper = $helper;
        $this->session = $session;
        $this->emulate = $emulate;
        $this->jsonHelper = $jsonHelper;
        $this->deviceToken = $deviceToken;
        $this->curl = $curl;
    }

    /**
     * Execute Function for class OrderStatusNotification
     *
     * @param \Magento\Framework\Event\Observer $observer $observer
     *
     * @return void|json
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $order = $observer->getOrder();
        if ($order->getState() != "") {
            $canReorder = 0;
            if ($this->helper->canReorder($order) == 1) {
                $canReorder = $this->helper->canReorder($order);
            }
            $environment = $this->emulate->startEnvironmentEmulation(
                $order->getStoreId(),
                \Magento\Framework\App\Area::AREA_ADMINHTML,
                true
            );
            $message = [
                "id" => $order->getId(),
                "body" => __("Your order status changed to ").$order->getStatusLabel(),
                "title" => __("Order Status Changed!!"),
                "sound" => "default",
                "message" => __("Your order status changed to ").$order->getStatusLabel(),
                "canReorder" => $canReorder,
                "incrementId" => $order->getIncrementId(),
                "notificationType" => "order"
            ];
            if ($order->getState() == "new") {
                $message["title"] = __("Order Placed Successfully!!");
                $message["message"] = __("Your order status is ").$order->getStatusLabel();
            }
            $url = ConstantRepo::NOTIFICATION_URL;
            $authKey = $this->helper->getConfigData(ConstantRepo::NOTIFICATION_API_KEY);
            $headers = [
                "Authorization:key=".$authKey,
                "Content-Type:application/json",
            ];
            if ($authKey != "") {
                $customerId = 0;
                if (!$order->getCustomerIsGuest()) {
                    $tokenCollection = $this->deviceToken->getCollection()->addFieldToFilter(
                        "customer_id",
                        $order->getCustomerId()
                    );
                } else {
                    $tokenCollection = $this->deviceToken->getCollection()->addFieldToFilter("customer_id", 0)
                        ->addFieldToFilter("email", $order->getCustomerEmail());
                }
                $this->getDeviceToken($tokenCollection, $message, $url, $headers, $order);
                $this->session->setNotificationStatus($order->getId().$order->getStatus());
            }
            $this->emulate->stopEnvironmentEmulation($environment);
        }
    }

    public function getDeviceToken($tokenCollection, $message, $url, $headers, $order)
    {
        foreach ($tokenCollection as $eachToken) {
            $this->helper->printLog($eachToken->getData());
            $fields = [
                "to" => $eachToken->getToken(),
                "data" => $message,
                "priority" => "high",
                "time_to_live" => 30,
                "delay_while_idle" => true,
                "content_available" => true
            ];
            if ($eachToken->getOs() == "ios") {
                $fields["notification"] = $message;
            }

            if ($order->getId().$order->getStatus() != $this->session->getNotificationStatus()) {
                $this->session->setCustomStatus($order->getId().$order->getStatusLabel());
                $this->curl->setHeaders($headers);
                $this->curl->post($url, json_encode($fields));
                $result = $this->curl->getBody();
                if ($this->isJson($result)) {
                    $result = $this->jsonHelper->jsonDecode($result);
                    if ($result["success"] == 0 && $result["failure"] == 1) {
                        $eachToken->delete();
                    }
                }
            }
        }
    }

    /**
     * Function isJson to check if a string is jSon
     *
     * @param string $string string
     *
     * @return bool
     */
    public function isJson($string)
    {
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }
}
