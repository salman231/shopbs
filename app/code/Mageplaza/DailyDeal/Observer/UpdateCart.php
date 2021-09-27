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
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\Quote;
use Mageplaza\DailyDeal\Helper\Data as HelperData;

/**
 * Class UpdateCart
 * @package Mageplaza\DailyDeal\Observer
 */
class UpdateCart implements ObserverInterface
{
    /**
     * @var HelperData
     */
    protected $_helperData;

    /**
     * UpdateCart constructor.
     *
     * @param HelperData $helperData
     */
    public function __construct(HelperData $helperData)
    {
        $this->_helperData = $helperData;
    }

    /**
     * @param Observer $observer
     *
     * @return $this
     * @throws LocalizedException
     */
    public function execute(Observer $observer)
    {
        if (!$this->_helperData->isEnabled()) {
            return $this;
        }

        /** @var Quote $quote */
        $quote = $observer->getCart()->getQuote();

        $allItems = $quote->getAllItems();
        foreach ($allItems as $item) {
            $productId = $item->getProduct()->getId();
            if ($this->_helperData->checkDealProduct($productId)) {
                $dealData  = $this->_helperData->getProductDeal($productId);
                $remainQty = $dealData->getDealQty() - $dealData->getSaleQty();
                $qty       = $item->getQty();
                if ($qty > $remainQty) {
                    throw new LocalizedException(
                        __('There\'s only %1 product(s) left, please try again with a smaller quantity', $remainQty)
                    );
                }
            }
        }

        return $this;
    }
}
