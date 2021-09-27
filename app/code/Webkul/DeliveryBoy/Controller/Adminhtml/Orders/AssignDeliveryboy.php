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
namespace Webkul\DeliveryBoy\Controller\Adminhtml\Orders;

use Magento\Framework\Exception\LocalizedException;

class AssignDeliveryboy extends \Magento\Backend\App\Action
{
    /**
     * Current order otp
     *
     * @var string
     */
    protected $otp = null;

    /**
     * Order already assigned flag
     *
     * @var int
     */
    protected $alreadyAssignedTo = 0;

    /**
     * @var \Magento\Framework\App\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;

    /**
     * @var \Webkul\DeliveryBoy\Model\Deliveryboy
     */
    protected $deliveryboy;

    /**
     * @var \Webkul\DeliveryBoy\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    protected $transportBuilder;

    /**
     * @var \Magento\Framework\Translate\Inline\StateInterface
     */
    protected $inlineTranslation;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Webkul\DeliveryBoy\Model\OrderFactory
     */
    protected $deliveryboyOrderFactory;

    /**
     * @var \Webkul\DeliveryBoy\Helper\Data
     */
    protected $deliveryboyHelper;

    /**
     * @var \Webkul\DeliveryBoy\Model\ResourceModel\Token\Collection
     */
    protected $tokenResourceCollection;

    /**
     * @var \Webkul\DeliveryBoy\Model\ResourceModel\Order\CollectionFactory
     */
    protected $collectionFactory;
    
    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \Webkul\DeliveryBoy\Helper\Operation
     */
    private $operationHelper;

    /**
     * @param \Webkul\DeliveryBoy\Helper\Data $helper
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Webkul\DeliveryBoy\Model\Deliveryboy $deliveryboy
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Webkul\DeliveryBoy\Helper\Data $deliveryboyHelper
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Webkul\DeliveryBoy\Model\OrderFactory $deliveryboyOrderFactory
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
     * @param \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation
     * @param \Magento\Framework\Controller\Result\JsonFactory $jsonFactory
     * @param \Webkul\DeliveryBoy\Model\ResourceModel\Token\Collection $tokenResourceCollection
     * @param \Webkul\DeliveryBoy\Model\ResourceModel\Order\CollectionFactory $collectionFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Webkul\DeliveryBoy\Helper\Operation $operationHelper
     */
    public function __construct(
        \Webkul\DeliveryBoy\Helper\Data $helper,
        \Magento\Backend\App\Action\Context $context,
        \Webkul\DeliveryBoy\Model\Deliveryboy $deliveryboy,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Webkul\DeliveryBoy\Helper\Data $deliveryboyHelper,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Webkul\DeliveryBoy\Model\OrderFactory $deliveryboyOrderFactory,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Framework\Controller\Result\JsonFactory $jsonFactory,
        \Webkul\DeliveryBoy\Model\ResourceModel\Token\Collection $tokenResourceCollection,
        \Webkul\DeliveryBoy\Model\ResourceModel\Order\CollectionFactory $collectionFactory,
        \Psr\Log\LoggerInterface $logger,
        \Webkul\DeliveryBoy\Helper\Operation $operationHelper
    ) {
        parent::__construct($context);

        $this->objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->deliveryboy = $deliveryboy;
        $this->helper = $helper;
        $this->jsonHelper = $jsonHelper;
        $this->orderFactory = $orderFactory;
        $this->transportBuilder = $transportBuilder;
        $this->inlineTranslation = $inlineTranslation;
        $this->storeManager = $storeManager;
        $this->jsonFactory = $jsonFactory;
        $this->deliveryboyOrderFactory = $deliveryboyOrderFactory;
        $this->deliveryboyHelper = $deliveryboyHelper;
        $this->tokenResourceCollection = $tokenResourceCollection;
        $this->collectionFactory = $collectionFactory;
        $this->logger = $logger;
        $this->operationHelper = $operationHelper;
    }

    /**
     * @return \Magetno\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $wholeData = $this->getRequest()->getParams();
        $incrementId = $wholeData["incrementId"] ?? 0;
        $deliveryboyId = $wholeData["deliveryboyId"] ?? 0;
        $resultJsonFactory = $this->jsonFactory;
        $result = $resultJsonFactory->create();
        if ($deliveryboyId) {
            $deliveryBoy = $this->deliveryboy->load($deliveryboyId);
            if ($deliveryBoy->getData("availability_status") == 0) {
                return $result->setData(2);
            }
        }
        $orderFactory = $this->orderFactory;
        $order = $orderFactory->create()->loadByIncrementId($incrementId);
        try {
            if (!$this->deliveryboyHelper->canAssignOrder($order)) {
                throw new LocalizedException(__(
                    'Unable to perform the requested operation. The order is in %1 state.',
                    $order->getState()
                ));
            }
            $assignedOrder = $this->verifyUsernData($incrementId);
            $assignedId = 0;
            if ($assignedOrder->getId() > 0) {
                $assignedId = $assignedOrder->getId();
                $this->alreadyAssignedTo = $assignedOrder->getDeliveryboyId();
            }
            $deliveryboyOrder = $this->deliveryboyOrderFactory->create();
            if ($assignedId != 0) {
                $deliveryboyOrder->setId($assignedId);
            }
            $deliveryboyOrder->setOtp($this->getOtp())
                ->setAssignStatus("")
                ->setOrderId($order->getId())
                ->setOrderStatus($order->getState())
                ->setDeliveryboyId($deliveryboyId)
                ->setIncrementId($order->getIncrementId())
                ->save();
            $this->sendEmail($deliveryboyId, $order);
            $this->sendAssignmentNotification($deliveryboyId, $order);
            // $this->logger->debug("divyassignmeden");
            if ($this->alreadyAssignedTo != 0) {
                $this->sendUnAssignmentNotification($deliveryboyId, $order);
            }
            return $result->setData(1);
        } catch (\Exception $e) {
            return $result->setData($e->getMessage());
        }
    }

    /**
     * @param int $incrementId
     * @return \Webkul\DeliveryBoy\Model\ResourceModel\Order\Collection
     */
    protected function verifyUsernData($incrementId)
    {
        $deliveryboyOrderCollection = $this->collectionFactory->create()
            ->addFieldToFilter("increment_id", $incrementId);
        $this->_eventManager->dispatch(
            'wk_deliveryboy_assigned_order_collection_apply_filter_event',
            [
                'deliveryboy_order_collection' => $deliveryboyOrderCollection,
                'collection_table_name' => 'main_table',
                'owner_id' => 0,
            ]
        );
        $deliveryboyOrder = $deliveryboyOrderCollection->getFirstItem();

        return $deliveryboyOrder;
    }

    /**
     * @return string
     */
    public function getOtp()
    {
        if (!$this->otp) {
            $i = 0;
            $pin = "";
            while ($i < 4) {
                $pin .= random_int(0, 9);
                $i++;
            }
            $this->otp = $pin;
        }
        return $this->otp;
    }

    /**
     * @return void
     */
    public function sendEmail($deliveryboyId, $order)
    {
        try {
            $deliveryboy = $this->deliveryboy->load($deliveryboyId);
            $deliveryboyName = $deliveryboy->getName();
            $templateVariables = [];
            $templateVariables["otp"] = $this->getOtp();
            $templateVariables["orderDate"] = $this->deliveryboyHelper->formatDateTimeCurrentLocale(
                $order->getCreatedAt()
            );
            $templateVariables["orderStatus"] = $order->getStatus();
            $templateVariables["customerName"] = $order->getCustomerFirstname() . " " . $order->getCustomerLastname();
            $templateVariables["deliveryboyName"] = $deliveryboyName;
            $templateVariables["orderIncrementId"] = $order->getIncrementId();
            $templateVariables["deliveryboyContact"] = $deliveryboy->getMobileNumber();
            $this->inlineTranslation->suspend();
            $senderInfo = [
                "name"  => "Admin",
                "email" => $this->helper->getGeneralEmail()
            ];
            $receiverInfo = [
                "name"  => $templateVariables["customerName"],
                "email" => $order->getCustomerEmail()
            ];
            $template = "deliveryboy_email_otp";
            $template = $this->transportBuilder->setTemplateIdentifier($template)
                ->setTemplateOptions(
                    [
                        "area"  => \Magento\Framework\App\Area::AREA_FRONTEND,
                        "store" => $this->storeManager->getStore()->getId(),
                    ]
                )
                ->setTemplateVars($templateVariables)
                ->setFrom($senderInfo)
                ->addTo($receiverInfo["email"], $receiverInfo["name"]);
            $transport = $this->transportBuilder->getTransport();
            $transport->sendMessage();
            $this->inlineTranslation->resume();
        } catch (\Exception $e) {
            $this->logger->debug($e->getMessage());
        }
    }

    /**
     * @return void
     */
    public function sendAssignmentNotification($deliveryboyId, $order)
    {
        $message = [
            "id" => $order->getId(),
            "body" => __("Your have received new order to deliver."),
            "title" => __("New Order Assigned."),
            "sound" => "default",
            "status" => $order->getStatus(),
            "message" => __("Your have received new order to deliver."),
            "incrementId" => $order->getIncrementId(),
            "notificationType" => "deliveryBoyNewOrder"
        ];
        $fields = [
            "data" => $message,
            "priority" => "high",
            "time_to_live" => 30,
            "delay_while_idle" => true,
            "content_available" => true
        ];
        $authKey = $this->deliveryboyHelper->getFcmApiKey();
        $this->logger->debug("divydebug: ".$authKey);
        if (empty($authKey)) {
            return ;
        }
        $headers = [
            "Authorization: key=" . $authKey,
            "Content-Type: application/json",
        ];
        $tokenCollection = $this->tokenResourceCollection
            ->addFieldToFilter("deliveryboy_id", $deliveryboyId);
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
    }

    /**
     * @return void
     */
    public function sendUnAssignmentNotification($deliveryboyId, $order)
    {
        $message = [
            "id" => $order->getId(),
            "body" => __("One order is unassigned form you."),
            "title" => __("Order UnAssigned."),
            "sound" => "default",
            "status" => $order->getStatus(),
            "message" => __("One order is unassigned form you."),
            "incrementId" => $order->getIncrementId(),
            "notificationType" => "orderUnassigned"
        ];
        $fields = [
            "data" => $message,
            "priority" => "high",
            "time_to_live" => 30,
            "delay_while_idle" => true,
            "content_available" => true
        ];
        $authKey = $this->deliveryboyHelper->getFcmApiKey();
        if (empty($authKey)) {
            return ;
        }
        $headers = [
            "Authorization: key=" . $authKey,
            "Content-Type: application/json",
        ];
        $tokenCollection = $this->tokenResourceCollection
            ->addFieldToFilter("deliveryboy_id", $this->alreadyAssignedTo);
        foreach ($tokenCollection as $eachToken) {
            $fields["to"] = $eachToken->getToken();
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
    }
}
