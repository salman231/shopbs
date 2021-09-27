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
 * @package     Mageplaza_RewardPoints
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\DailyDeal\Observer;

use Exception;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order\Creditmemo;
use Mageplaza\DailyDeal\Helper\Data as HelperData;
use Mageplaza\DailyDeal\Model\ResourceModel\DealFactory;

/**
 * Class CreditmemoRefundSaveAfter
 * @package Mageplaza\RewardPoints\Observer
 */
class OrderCreditMemoAfter implements ObserverInterface
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
     * OrderCreditMemoAfter constructor.
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

        /* @var $creditmemo Creditmemo */
        $creditmemo = $observer->getEvent()->getCreditmemo();
        $order      = $creditmemo->getOrder();
        $items      = $order->getAllVisibleItems();

        foreach ($items as $item) {
            $sku       = $item->getSku();
            $productId = $this->_helperData->getProductIdBySku($sku);
            $qtyRefund = $item->getQtyRefunded();
            if ($productId === null) {
                return $this;
            }
            if ($this->_helperData->checkDealProduct($productId)) {
                $this->_dealFactory->create()->updateRunningSaleQty($productId, -$qtyRefund);
            } elseif ($this->_helperData->checkEndedDeal($productId)) {
                $this->_dealFactory->create()->updateSaleQty($productId, -$qtyRefund);
            }
        }

        return $this;
    }
}
