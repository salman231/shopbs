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

use Magento\Catalog\Model\Product;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\Quote\Item;
use Mageplaza\DailyDeal\Helper\Data as HelperData;

/**
 * Class SetDiscountPriceDealInCart
 * @package Mageplaza\DailyDeal\Observer
 */
class SetPriceDealInCart implements ObserverInterface
{
    /**
     * @var HelperData
     */
    protected $_helperData;

    /**
     * @var RequestInterface
     */
    protected $_request;

    /**
     * @var Product
     */
    protected $_product;

    /**
     * SetPriceDealInCart constructor.
     *
     * @param HelperData $helperData
     * @param RequestInterface $request
     * @param Product $product
     */
    public function __construct(
        HelperData $helperData,
        RequestInterface $request,
        Product $product
    ) {
        $this->_helperData = $helperData;
        $this->_request    = $request;
        $this->_product    = $product;
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

        $productId = 0;
        /** @var Item $item */
        $item = $observer->getEvent()->getData('quote_item');

        /** check configuration product **/
        if ($item->getHasChildren()) {
            foreach ($item->getChildren() as $child) {
                $productId = $child->getProduct()->getId();
            }
        } else {
            $productId = $item->getProduct()->getId();
        }

        if ($this->_helperData->checkDealProduct($productId)) {
            $dealData     = $this->_helperData->getProductDeal($productId);
            $remainQty    = $dealData->getDealQty() - $dealData->getSaleQty();
            $qty          = $item->getQty();
            $optionPrices = 0;

            if ($qty <= $remainQty) {
                /** check customizable option */
                $product = $this->_product->load($productId);

                if (!empty($product->getOptions())) {
                    foreach ($product->getOptions() as $option) {
                        $optionPrices += (float) $option->getPrice();
                    }
                }

                $price     = $this->_helperData->getDealPrice($productId);
                $dealPrice = $price + $optionPrices;
                $item->setOriginalCustomPrice($dealPrice);
                $item->getProduct()->setIsSuperMode(true);
            } else {
                throw new LocalizedException(
                    __('There\'s only %1 product(s) left, please try again with a smaller quantity', $remainQty)
                );
            }
        } else {
            $item->setOriginalCustomPrice(null);
            $item->getProduct()->setIsSuperMode(true);
        }

        return $this;
    }
}
