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

namespace Mageplaza\DailyDeal\Plugin\Product;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\Quote\Item;
use Magento\Quote\Model\Quote\Item\AbstractItem;
use Mageplaza\DailyDeal\Helper\Data as HelperData;

/**
 * Class SetPriceItem
 * @package Mageplaza\DailyDeal\Plugin\Product
 */
class SetPriceItem
{
    protected $_helperData;

    /**
     * CheckUpdateQty constructor.
     *
     * @param HelperData $helperData
     */
    public function __construct(
        HelperData $helperData
    ) {
        $this->_helperData = $helperData;
    }

    /**
     * @param AbstractItem $subject
     *
     * @throws NoSuchEntityException
     */
    public function beforeGetCalculationPriceOriginal(AbstractItem $subject)
    {
        /** @var $item Item */
        if ($this->_helperData->isEnabled()) {
            foreach ($subject->getQuote()->getAllItems() as $item) {
                $id        = $item->getProduct()->getId();
                $productId = '';
                if ($item->getHasChildren()) {
                    /** get child product id configuration product **/
                    foreach ($item->getChildren() as $child) {
                        $childId = $child->getProduct()->getId();
                        if ($this->_helperData->checkDealProduct($childId)) {
                            $productId = $childId;
                        }
                    }
                } elseif ($this->_helperData->checkDealProduct($id)) {
                    $productId = $id;
                }
                if ($productId && !empty($productId)) {
                    $dealData  = $this->_helperData->getProductDeal($productId);
                    $remainQty = $dealData->getDealQty() - $dealData->getSaleQty();
                    $qty       = $item->getQty();
                    if ($qty <= $remainQty) {
                        /** check customizable option */
                        $product = $item->getProduct();

                        $optionPrices = 0;
                        if (!empty($product->getOptions())) {
                            foreach ($product->getOptions() as $option) {
                                $optionPrices += (float) $option->getPrice();
                            }
                        }

                        $price     = $this->_helperData->getDealPrice($productId);
                        $dealPrice = $price + $optionPrices;
                        $item->setOriginalCustomPrice($dealPrice);
                        $item->getProduct()->setIsSuperMode(true);
                    }
                } /*else {
                    $item->setOriginalCustomPrice(null);
                }*/
            }
        } /*else {
            foreach ($subject->getQuote()->getAllItems() as $item) {
                $item->setOriginalCustomPrice(null);
            }
        }*/
    }
}
