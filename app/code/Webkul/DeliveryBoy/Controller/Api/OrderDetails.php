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
namespace Webkul\DeliveryBoy\Controller\Api;

use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Model\ResourceModel\Order\Item\Collection as ItemCollection;
use Magento\Sales\Block\Order\Totals as MagentoOrderTotalsBlock;

class OrderDetails extends AbstractDeliveryboy
{
    const MAGE_STATUS_PENDING = 'pending';

    /**
     * @var \Webkul\DeliveryBoy\Model\ResourceModel\Order\Collection
     */
    protected $deliveryboyOrderResourceCollectionInstance;

    /**
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        try {
            $this->verifyRequest();
            $this->resetDeliveryboyOrderResourceCollection(false);
            $this->validateRequestData();
            $environment  = $this->emulate->startEnvironmentEmulation($this->storeId);
            $this->verifyUsernData();
            if ($this->order->getId()) {
                $this->getOrderItems($this->order);
                $totals = $this->getFormattedTotals();
                $this->returnArray["totals"] = $totals;
                $this->returnArray["customerName"] = $this->order->getCustomerName();
                $this->returnArray["customerId"] = $this->order->getCustomerId();
                $infoBlock = $this->orderInfoBlock;
                if ($this->order->getShippingAddress()) {
                    $this->returnArray["customerEmail"] = $this->order->getShippingAddress()->getEmail();
                    $this->returnArray["formattedAddress"] = $infoBlock
                        ->getFormattedAddress($this->order->getShippingAddress());
                    $this->returnArray["customerTelephone"] = $this->order->getShippingAddress()->getTelephone();
                } else {
                    $this->returnArray["customerEmail"] = $this->order->getBillingAddress()->getEmail();
                    $this->returnArray["formattedAddress"] = $infoBlock
                        ->getFormattedAddress($this->order->getBillingAddress());
                    $this->returnArray["customerTelephone"] = $this->order->getBillingAddress()->getTelephone();
                }
                $this->returnArray["date"] = $this->timezone
                    ->date(new \DateTime($this->order->getCreatedAt()))->format("M d, Y h:i:s A");
                if ($this->order->getPayment()->getMethodInstance()->getCode() == "cashondelivery") {
                    $this->returnArray["hasCOD"] = true;
                } else {
                    $this->returnArray["hasCOD"] = false;
                }
                $this->returnArray["orderTotal"] = $this->helperCatalog
                    ->stripTags($this->order->formatPrice($this->order->getGrandTotal()));
                $commentCollection = $this->commentCollection->create();
                $commentCollection->addFieldToFilter("order_increment_id", $this->incrementId);
                $commentCollection->setOrder("created_at", "DESC");
                $this->returnArray["totalCommentCount"] = $commentCollection->getSize();
                $commentCollection->setPageSize(5)->setCurPage(1);
                $commentList =[];
                foreach ($commentCollection as $each) {
                    $eachComment = [];
                    $eachComment["comment"] = $each->getComment();
                    $eachComment["createdAt"] = $this->timezone
                        ->date(new \DateTime($each->getCreatedAt()))->format("M d, Y h:i:s A");
                    $eachComment["commentedBy"] = $each->getCommentedBy();
                    $commentList[] = $eachComment;
                }
                $this->returnArray['pickUpLatitude'] = $this->deliveryboyHelper->getLatitude();
                $this->returnArray['pickUpLongitude'] = $this->deliveryboyHelper->getLongitude();
                $this->returnArray["commentList"] = $commentList;
                $this->returnArray["success"] = true;
            } else {
                $this->returnArray["message"] = __("Invalid Order.");
            }
            $this->emulate->stopEnvironmentEmulation($environment);
        } catch (\Throwable $e) {
            $this->returnArray["message"] = __($e->getMessage());
        }
        
        return $this->getJsonResponse($this->returnArray);
    }

    /**
     * @return void
     * @throws LocalizedException
     */
    protected function verifyUsernData()
    {
        $this->order = $this->orderFactory->create()->loadByIncrementId($this->incrementId);
        if ($this->order->getId()) {
            $collection = $this->getDeliveryboyOrderResourceCollection()
                ->addFieldToSelect('*')
                ->addFieldToFilter("increment_id", $this->incrementId);
            if ($this->isDeliveryboy) {
                $collection->addFieldToFilter("deliveryboy_id", $this->userId);
                $deliveryboyId = $collection->getFirstItem()->getDeliveryboyId();
                if ($deliveryboyId != $this->userId) {
                    throw new LocalizedException(__("Unauthorized access."));
                }
            }
            $deliveryBoyOrder = $collection->getFirstItem();
            $this->returnArray["picked"] = (bool)$deliveryBoyOrder['picked'];
            $orderStatus = [];
            $statusLabel = [];
            $statusCollection = $this->orderStatusCollection
                ->getResourceCollection()
                ->getData();
            foreach ($statusCollection as $status) {
                $statusLabel[$status["status"]] = $status["label"];
                $orderStatus[] = $status;
            }
            $mageOrderNew = \Magento\Sales\Model\Order::STATE_NEW;
            $dbOrderStatus = $deliveryBoyOrder->getOrderStatus() === $mageOrderNew
                ? self::MAGE_STATUS_PENDING
                : $deliveryBoyOrder->getOrderStatus();
            $dbOrderState = $deliveryBoyOrder->getOrderStatus() === $mageOrderNew
                ? $deliveryBoyOrder->getOrderStatus()
                : $deliveryBoyOrder->getOrderStatus();
            $this->returnArray["status"] = $statusLabel[$dbOrderStatus];
            $this->returnArray["state"] = $dbOrderState;
            $this->returnArray["deliveryboyId"] = 0;
            $this->returnArray["deliveryboyName"] = "";
            if ($deliveryBoyOrder->getId()) {
                $this->returnArray["id"] = $deliveryBoyOrder->getId();
                $this->returnArray["deliveryboyId"] = $deliveryBoyOrder->getDeliveryboyId();
                $deliveryBoy = $this->deliveryboyResourceCollection->create()
                    ->addFieldToSelect("name")
                    ->addFieldToSelect("id")
                    ->addFieldToFilter("id", $this->returnArray["deliveryboyId"])
                    ->getFirstItem();
                $this->returnArray["deliveryboyName"] = $deliveryBoy->getName();
                if (!$this->isDeliveryboy) {
                    $this->getDeliveryboyRating();
                }
            }
        }
    }

    /**
     * @return void
     */
    protected function getDeliveryboyRating()
    {
        $ratings = $this->ratingCollection->create()
            ->addFieldToSelect("deliveryboy_id")
            ->addFieldToSelect("rating")
            ->addFieldToSelect("status")
            ->addFieldToFilter("status", 1)
            ->addFieldToFilter("deliveryboy_id", $this->returnArray["deliveryboyId"]);
        $ratings->getSelect()
            ->columns(
                [
                    "avg" => new \Zend_Db_Expr("AVG(rating)")
                ]
            );
        foreach ($ratings as $each) {
            $this->returnArray["deliveryboyRating"] = $each->getAvg();
        }
    }

    /**
     * @return void
     * @throws LocalizedException
     */
    public function verifyRequest()
    {
        if ($this->getRequest()->getMethod() == "GET" && $this->wholeData) {
            $this->width = trim($this->wholeData["width"] ?? 1000);
            $this->storeId = trim($this->wholeData["storeId"] ?? 1);
            $this->incrementId = trim($this->wholeData["incrementId"] ?? "");
            $this->userId = trim($this->wholeData["userId"] ?? 0);
            $this->isDeliveryboy = trim($this->wholeData["isDeliveryboy"] ?? false);
            $this->adminCustomerEmail = trim($this->wholeData["adminCustomerEmail"] ?? "");
            $this->resetDeliveryboyOrderResourceCollection(false);
        } else {
            throw new LocalizedException(__("Invalid Request"));
        }
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @return void
     */
    public function getOrderItems($order)
    {
        $items = $order->getItemsCollection();
        $items = $this->addIntermediateFIltersSalesOrderItemCollection($items);
        $itemBlock = $this->orderItemRenderer;
        $priceBlock = $this->priceRenderer;
        $itemList = [];
        foreach ($items as $item) {
            if ($this->isDeliveryboy && in_array($item->getProduct()->gettypeId(), ['virtual','downloadable'])) {
                continue;
            }
            if (!$this->isItemAllowed($item->getProductId(), $order->getId())) {
                continue;
            }
            $itemBlock->setItem($item);
            $priceBlock->setItem($item);
            if ($item->getParentItem()) {
                continue;
            }
            $eachItem = [];
            $eachItem["name"] = $itemBlock->escapeHtml($item->getName());
            $eachItem["thumbNail"] = $this->helperCatalog->getImageUrl(
                $item->getProduct(),
                (int)$this->width/2.5,
                "product_page_image_small"
            );
            if ($options = $itemBlock->getItemOptions()) {
                foreach ($options as $option) {
                    $eachOption = [];
                    $eachOption["label"] = $itemBlock->escapeHtml($option["label"]);
                    if (!$itemBlock->getPrintStatus()) {
                        $formatedOptionValue = $itemBlock->getFormatedOptionValue($option);
                        if (isset($formatedOptionValue["full_view"])) {
                            $eachOption["value"] = $formatedOptionValue["full_view"];
                        } else {
                            $eachOption["value"] = $formatedOptionValue["value"];
                        }
                    } else {
                        $eachOption["value"] = nl2br($itemBlock->escapeHtml(
                            $option["print_value"] ?? $option["value"]
                        ));
                    }
                    $eachItem["option"][] = $eachOption;
                }
            } else {
                $eachItem["option"] = [];
            }
            $eachItem["sku"] = $itemBlock->prepareSku($itemBlock->getSku());
            if ($priceBlock->displayPriceInclTax() || $priceBlock->displayBothPrices()) {
                $eachItem["price"] = $order->formatPriceTxt($itemBlock->getUnitDisplayPriceInclTax());
            }
            if ($priceBlock->displayPriceExclTax() || $priceBlock->displayBothPrices()) {
                $eachItem["price"] = $order->formatPriceTxt($priceBlock->getUnitDisplayPriceExclTax());
            }
            $eachItem["qty"]["Ordered"] = $itemBlock->getItem()->getQtyOrdered()*1;
            $eachItem["qty"]["Shipped"] = $itemBlock->getItem()->getQtyShipped()*1;
            $eachItem["qty"]["Canceled"] = $itemBlock->getItem()->getQtyCanceled()*1;
            $eachItem["qty"]["Refunded"] = $itemBlock->getItem()->getQtyRefunded()*1;
            if (($priceBlock->displayPriceInclTax() || $priceBlock->displayBothPrices()) && !$item->getNoSubtotal()) {
                $eachItem["subTotal"] = $order->formatPriceTxt($priceBlock->getRowDisplayPriceInclTax());
            }
            if ($priceBlock->displayPriceExclTax() || $priceBlock->displayBothPrices()) {
                $eachItem["subTotal"] = $order->formatPriceTxt($priceBlock->getRowDisplayPriceExclTax());
            }
            $itemList[] = $eachItem;
        }
        $this->returnArray["itemList"] = $itemList;
    }

    /**
     * @return \Webkul\DeliveryBoy\Model\ResourceModel\Order\Collection
     */
    protected function getDeliveryboyOrderResourceCollection()
        : \Webkul\DeliveryBoy\Model\ResourceModel\Order\Collection
    {
        if (!($this->deliveryboyOrderResourceCollectionInstance instanceof
            \Webkul\DeliveryBoy\Model\ResourceModel\Order\Collection)
        ) {
            $this->deliveryboyOrderResourceCollectionInstance = $this->deliveryboyOrderResourceCollection->create();
        }

        return $this->deliveryboyOrderResourceCollectionInstance;
    }

    /**
     * @param bool $onlyWhere
     * @return void
     */
    protected function resetDeliveryboyOrderResourceCollection(bool $onlyWhere = true)
    {
        if (($deliveryboyOrderResourceCollection = $this->getDeliveryboyOrderResourceCollection())) {
            $select = $deliveryboyOrderResourceCollection
                ->getSelect();
                $select->reset(\Zend_Db_Select::WHERE);
            if (!$onlyWhere) {
                $select->reset(\Zend_Db_Select::COLUMNS);
            }
        }
    }

    /**
     * @return void
     * @throws LocalizedException
     */
    protected function validateRequestData()
    {
        if (!(
        ($this->adminCustomerEmail === $this->deliveryboyHelper->getAdminEmail())
        || ($this->isDeliveryboy && $this->userId > 0)
        )) {
            throw new LocalizedException(__("Unauthorized access."));
        }
    }

    /**
     * @param ItemCollection $itemsCollection
     * @return ItemCollection
     */
    protected function addIntermediateFIltersSalesOrderItemCollection(
        ItemCollection $itemsCollection
    ): ItemCollection {

        return $itemsCollection;
    }

    /**
     * @param int $productId
     * @param int $orderId
     * @return bool
     */
    protected function isItemAllowed(int $productId, int $orderId): bool
    {
        return true;
    }

    /**
     * @return array
     */
    protected function getformattedTotals(): array
    {
        $totalsBlock = $this->getOrderTotalsBlock();
        $totalsBlock->setOrder($this->order);
        $totalsBlock->_initTotals();
        $totals = [];
        foreach ($totalsBlock->getTotals() as $total) {
            $eachTotal = [];
            $eachTotal["code"] = $total->getCode();
            $eachTotal["label"] = $total->getLabel();
            $eachTotal["value"] = $this->helperCatalog->stripTags($total->getValue());
            $eachTotal["formattedValue"] = $this->helperCatalog->stripTags($totalsBlock->formatValue($total));
            $totals[] = $eachTotal;
        }
        $tax = array_filter($totals, function ($total) {
            return ($total['code'] === 'tax');
        });
        if (!count($tax)) {
            $eachTotal = [];
            $eachTotal["code"] = "tax";
            $eachTotal["label"] = __("Tax");
            $eachTotal["value"] = $this->order->getTaxAmount();
            $eachTotal["formattedValue"] = $this->order->formatPriceTxt($this->order->getTaxAmount());
            $totals[] = $eachTotal;
        }

        return $totals;
    }

    /**
     * @return MagentoOrderTotalsBlock
     */
    protected function getOrderTotalsBlock(): MagentoOrderTotalsBlock
    {
        return $this->orderTotals;
    }
}
