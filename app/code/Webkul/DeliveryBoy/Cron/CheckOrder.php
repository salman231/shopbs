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
namespace Webkul\DeliveryBoy\Cron;

class CheckOrder
{
    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    private $jsonHelper;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    private $connection;

    /**
     * @var \Webkul\DeliveryBoy\Model\ResourceModel\Token\CollectionFactory
     */
    private $deliveryboyTokenResourceCollectionFactory;
    
    /**
     * @var \Webkul\DeliveryBoy\Model\ResourceModel\Order\CollectionFactory
     */
    private $deliveryboyOrderResourceCollectionFactory;

    /**
     * @var \Webkul\DeliveryBoy\Helper\Operation
     */
    private $operationHelper;

    /**
     * @var \Webkul\DeliveryBoy\Helper\Data
     */
    private $deliveryboyDataHelper;
    
    /**
     * @param \Webkul\DeliveryBoy\Helper\Operation $operationHelper
     * @param \Webkul\DeliveryBoy\Helper\Data $deliveryboyDataHelper
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Magento\Framework\App\ResourceConnection $connection
     * @param \Webkul\DeliveryBoy\Model\ResourceModel\Token\CollectionFactory $deliveryboyTokenResourceCollectionFactory
     * @param \Webkul\DeliveryBoy\Model\ResourceModel\Order\CollectionFactory $deliveryboyOrderResourceCollectionFactory
     */
    public function __construct(
        \Webkul\DeliveryBoy\Helper\Operation $operationHelper,
        \Webkul\DeliveryBoy\Helper\Data $deliveryboyDataHelper,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Framework\App\ResourceConnection $connection,
        \Webkul\DeliveryBoy\Model\ResourceModel\Token\CollectionFactory $deliveryboyTokenResourceCollectionFactory,
        \Webkul\DeliveryBoy\Model\ResourceModel\Order\CollectionFactory $deliveryboyOrderResourceCollectionFactory
    ) {
        $this->operationHelper = $operationHelper;
        $this->deliveryboyDataHelper = $deliveryboyDataHelper;
        $this->jsonHelper = $jsonHelper;
        $this->connection = $connection;
        $this->deliveryboyTokenResourceCollectionFactory = $deliveryboyTokenResourceCollectionFactory;
        $this->deliveryboyOrderResourceCollectionFactory = $deliveryboyOrderResourceCollectionFactory;
    }

    /**
     * @return void
     */
    public function execute()
    {
        $orderCollection = $this->deliveryboyOrderResourceCollectionFactory
            ->create()
            ->addFieldToFilter("assign_status", ["nin" => ["0", "1"]])
            ->addFieldToFilter("deliveryboy_id", ["gt" => 0]);
        
        $salesTable = $this->connection->getTableName("sales_order");
        $orderCollection->getSelect()
            ->join(
                [
                    "salesOrder" => $salesTable
                ],
                "main_table.order_id=salesOrder.entity_id",
                [
                    "shipping_method" => "shipping_method"
                ]
            );
        $allowedShipping = explode(
            ",",
            $this->deliveryboyDataHelper->getAllowedShipping()
        );
        $orderCollection->addFieldToFilter(
            "shipping_method",
            [
                "in" => $allowedShipping
            ]
        );
        $authKey = $this->deliveryboyDataHelper->getFcmApiKey();
        if (empty($authKey)) {
            return ;
        }
        $headers = [
            "Authorization: key=" . $authKey,
            "Content-Type: application/json",
        ];
        $tokenCollection = $this->deliveryboyTokenResourceCollectionFactory
            ->create()
            ->addFieldToFilter("is_admin", 1);
        $message = [
            "title" => __("Please reassign this Unclaimed Order"),
            "sound" => "default",
            "message" => __("Please reassign this Unclaimed Order"),
            "notificationType" => "orderStatus"
        ];
        $fields = [
            "data" => $message,
            "priority" => "high",
            "time_to_live" => 30,
            "delay_while_idle" => true,
            "content_available" => true
        ];
        if ($authKey != "") {
            foreach ($orderCollection as $order) {
                $orderIncrementId = $order->getIncrementId();
                $message['id'] = $orderIncrementId;
                $message["body"] = __("Unclaimed Order") . " #" . $orderIncrementId;
                $message['status'] = $order->getStatus();
                $message['incrementId'] = $orderIncrementId;
                
                foreach ($tokenCollection as $eachToken) {
                    $fields['to'] = $eachToken->getToken();
                    $fields["data"] = $message;
                    if ($eachToken->getOs() == "ios") {
                        $fields["notification"] = $message;
                    }
                    $result = $this->operationHelper->send($headers, $fields);
                    if (count($result) !== 0) {
                        if ($result["success"] == 0 && $result["failure"] == 1) {
                            $eachToken->delete();
                        }
                    }
                }
            }
        }
    }

    /**
     * @param string $string is any string to test
     * @return bool
     */
    public function isJson($string)
    {
        json_decode($string);
        return (json_last_error() === JSON_ERROR_NONE);
    }
}
