<?php
namespace WeSupply\Toolbox\Observer;

use Magento\Framework\App\Request\Http;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use WeSupply\Toolbox\Api\OrderInfoBuilderInterface;
use WeSupply\Toolbox\Api\OrderRepositoryInterface;
use WeSupply\Toolbox\Helper\Data as WeSupplyHelper;
use WeSupply\Toolbox\Logger\Logger as Logger;
use WeSupply\Toolbox\Model\Order;

class UpdateOrderInfo implements ObserverInterface
{
    /**
     * @var OrderRepositoryInterface
     */
    protected $weSupplyOrderRepository;

    /**
     * @var Order
     */
    protected $weSupplyOrderFactory;

    /**
     * @var OrderInfoBuilderInterface
     */
    protected $orderInfoBuilder;

    /**
     * @var Http
     */
    protected $request;

    /**
     * @var Json
     */
    protected $json;

    /**
     * @var WeSupplyHelper
     */
    protected $helper;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var TimezoneInterface
     */
    protected $timezone;

    /**
     * UpdateOrderInfo constructor.
     * @param OrderRepositoryInterface $weSupplyOrderRepository
     * @param Order $weSupplyOrderFactory
     * @param OrderInfoBuilderInterface $orderInfoBuilder
     * @param Http $request
     * @param Json $json
     * @param WeSupplyHelper $helper
     * @param Logger $logger
     * @param TimezoneInterface $timezone
     */
    public function __construct(
        OrderRepositoryInterface $weSupplyOrderRepository,
        Order $weSupplyOrderFactory,
        OrderInfoBuilderInterface $orderInfoBuilder,
        Http $request,
        Json $json,
        WeSupplyHelper $helper,
        Logger $logger,
        TimezoneInterface $timezone
    )
    {
        $this->weSupplyOrderRepository = $weSupplyOrderRepository;
        $this->weSupplyOrderFactory = $weSupplyOrderFactory;
        $this->orderInfoBuilder = $orderInfoBuilder;
        $this->request = $request;
        $this->json = $json;
        $this->helper = $helper;
        $this->logger = $logger;
        $this->timezone = $timezone;
    }

    /**
     * @param Observer $observer
     * @return $this|void
     */
    public function execute(Observer $observer)
    {
        $orderId = $observer->getData('orderId');
        try {
            $weSupplyOrder = $this->weSupplyOrderRepository->getByOrderId($orderId);
            if ($weSupplyOrder) {
                $existingOrderXml = simplexml_load_string($weSupplyOrder->getInfo(), 'SimpleXMLElement');
                $jsonOrderData = $this->json->serialize($existingOrderXml);
                $existingOrderData = $this->json->unserialize($jsonOrderData);
            }

            $orderData = $this->orderInfoBuilder->gatherInfo($orderId, $existingOrderData ?? null);
            if (empty($orderData)) {
                $this->logger->error("WeSupply Error: OrderInfo gathering with order id $orderId is empty");
                return $this;
            }

            $orderInfo = $this->orderInfoBuilder->prepareForStorage($orderData);

            $weSupplyOrder->setOrderId($orderId);
            $weSupplyOrder->setStoreId($this->orderInfoBuilder->getStoreId($orderData));
            $weSupplyOrder->setInfo($orderInfo);
            /**
             * set is_excluded flag only for the newly placed order
             */
            if ($this->request->getFrontName() !== 'admin') {
                if (!$weSupplyOrder->isExcluded()) {
                    if ($this->helper->orderExportExcludeAll()) {
                        $weSupplyOrder->setIsExcluded(1);
                    }
                    if ($this->helper->hasOrderExportRules()) {
                        $filters = $this->helper->getOrderExportRules();
                        foreach ($filters as $attribute => $filterVal) {
                            if (!$filterVal || empty($filterVal)) {
                                continue;
                            }

                            $filterByArr = explode(',', $filterVal);
                            $compareVal = $orderData[$attribute];
                            if (in_array($compareVal, $filterByArr)) {
                                $weSupplyOrder->setIsExcluded(1);
                                // stop if the condition is met
                                break;
                            }
                        }
                    }
                }
            }
            /**
             * updated at in default Magento 2 UTC
             */
            $weSupplyOrder->setUpdatedAt(date("Y-m-d H:i:s"));
            $this->weSupplyOrderRepository->save($weSupplyOrder);
        } catch (\Exception $ex) {
            $this->logger->error("WeSupply Error: " . $ex->getMessage());
        }

        return $this;
    }
}
