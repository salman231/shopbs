<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MobikulApi
 * @author    Webkul <support@webkul.com>
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html ASL Licence
 * @link      https://store.webkul.com/license.html
 */

namespace Webkul\MobikulApi\Plugin;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;

/**
 * OrderPlaceAfter class observer to set order purchase point
 */
class OrderPlaceAfter implements ObserverInterface
{
    /**
     * Constructor function
     *
     * @param \Webkul\MobikulCore\Model\OrderPurchasePointFactory $orderPurchaseFactory
     * @param \Magento\Framework\UrlInterface $urlInterface
     */
    public function __construct(
        \Webkul\MobikulCore\Model\OrderPurchasePointFactory $orderPurchaseFactory,
        \Magento\Framework\UrlInterface $urlInterface
    ) {
        $this->orderPurchaseFactory = $orderPurchaseFactory;
        $this->urlInterface = $urlInterface;
    }

    /**
     * Execute function to set purchase point
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        $url = $this->urlInterface->getCurrentUrl();
        if (stripos($url, "mobikulhttp") === false && $order) {
            $purchasePointColl = $this->orderPurchaseFactory->create()
            ->getCollection()
            ->addFieldToFilter("order_id", $order->getEntityId())
            ->getFirstItem();

            if ($purchasePointColl->getEntityId()) {
                $purchasePoint = $this->orderPurchaseFactory->create()->load($purchasePointColl->getEntityId());
                $purchasePoint->setIncrementId($order->getIncrementId());
                $purchasePoint->setOrderId($order->getEntityId());
                $purchasePoint->setPurchasePoint('web');
                $purchasePoint->save();
            } else {
                $purchasePoint = $this->orderPurchaseFactory->create();
                $purchasePoint->setIncrementId($order->getIncrementId());
                $purchasePoint->setOrderId($order->getEntityId());
                $purchasePoint->setPurchasePoint('web');
                $purchasePoint->save();
            }
        }
    }
}
