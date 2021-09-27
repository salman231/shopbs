<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_DailyDeal
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\DailyDeal\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order;
use Mageplaza\DailyDeal\Helper\Data;
use Mageplaza\DailyDeal\Model\ResourceModel\DealFactory;

/**
 * Class CheckoutOnePageSuccessAction
 * @package Mageplaza\DailyDeal\Observer
 */
class CheckoutOnePageSuccessAction implements ObserverInterface
{
    /**
     * @var ObserverInterface
     */
    protected $_order;

    /**
     * @var Data
     */
    protected $_helperData;

    /**
     * @var DealFactory
     */
    protected $_dealFactory;

    /**
     * CheckoutOnePageSuccessAction constructor.
     *
     * @param OrderInterface $order
     * @param Data $helperData
     * @param DealFactory $dealFactory
     */
    public function __construct(
        OrderInterface $order,
        Data $helperData,
        DealFactory $dealFactory
    ) {
        $this->_order       = $order;
        $this->_helperData  = $helperData;
        $this->_dealFactory = $dealFactory;
    }

    /**
     * @param Observer $observer
     *
     * @return $this
     */
    public function execute(Observer $observer)
    {
        if (!$this->_helperData->isEnabled()) {
            return $this;
        }

        /** @var array $orderIds */
        $orderIds = $observer->getEvent()->getOrderIds();
        foreach ($orderIds as $orderId) {
            /** @var Order $order */
            $order = $this->_order->load($orderId);
            $items = $order->getItemsCollection();
            foreach ($items as $item) {
                $productId = $item->getProductId();
                $qty       = $item->getQtyOrdered();

                $this->_dealFactory->create()->updateRunningSaleQty($productId, $qty);
            }
        }

        return $this;
    }
}
