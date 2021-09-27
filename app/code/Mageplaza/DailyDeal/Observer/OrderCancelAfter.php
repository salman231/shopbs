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

use Exception;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order;
use Mageplaza\DailyDeal\Helper\Data as HelperData;
use Mageplaza\DailyDeal\Model\ResourceModel\DealFactory;

/**
 * Class OrderCancelAfter
 * @package Mageplaza\DailyDeal\Observer
 */
class OrderCancelAfter implements ObserverInterface
{
    /**
     * @var HelperData
     */
    protected $_helperData;

    /**
     * @var DealFactory
     */
    protected $_dealFactory;

    /**
     * OrderCancelAfter constructor.
     *
     * @param DealFactory $dealFactory
     * @param HelperData $helperData
     */
    public function __construct(
        DealFactory $dealFactory,
        HelperData $helperData
    ) {
        $this->_dealFactory = $dealFactory;
        $this->_helperData  = $helperData;
    }

    /**
     * @param EventObserver $observer
     *
     * @return $this
     * @throws Exception
     */
    public function execute(EventObserver $observer)
    {
        if (!$this->_helperData->isEnabled()) {
            return $this;
        }

        /* @var $order Order */
        $order = $observer->getEvent()->getOrder();
        $items = $order->getAllVisibleItems();

        /** @var Order\Item $item */
        foreach ($items as $item) {
            $sku       = $item->getSku();
            $productId = $this->_helperData->getProductIdBySku($sku);
            $qtyCancel = $item->getQtyCanceled();
            if ($productId === null) {
                return $this;
            }
            if ($this->_helperData->checkDealProduct($productId)) {
                $this->_dealFactory->create()->updateRunningSaleQty($productId, -$qtyCancel);
            } else {
                if ($this->_helperData->checkEndedDeal($productId)) {
                    $this->_dealFactory->create()->updateSaleQty($productId, -$qtyCancel);
                }
            }
        }

        return $this;
    }
}
