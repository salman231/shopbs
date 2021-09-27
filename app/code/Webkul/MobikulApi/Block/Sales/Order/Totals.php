<?php
/**
 * Webkul Software.
 *
 *
 *
 * @category  Webkul
 * @package   Webkul_MobikulApi
 * @author    Webkul <support@webkul.com>
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html ASL Licence
 * @link      https://store.webkul.com/license.html
 */

namespace Webkul\MobikulApi\Block\Sales\Order;

/**
 * Class Totals in Sales Order Block
 */
class Totals extends \Magento\Sales\Block\Order\Totals
{
    /**
     * Fucntion _initotals in Totals Class to return the Total Details
     *
     * @return object $this
     */
    public function _initTotals()
    {
        $source = $this->getSource();
        $this->_totals = [];
        $this->_totals["subtotal"] = new \Magento\Framework\DataObject(
            [
                "code" => "subtotal", "value" => $source->getSubtotal(), "label" => __("Subtotal")
            ]
        );

        if (!$source->getIsVirtual() && ((double)$source->getShippingAmount() || $source->getShippingDescription())) {
            $this->_totals["shipping"] = new \Magento\Framework\DataObject(
                [
                    "code" => "shipping",
                    "field" => "shipping_amount",
                    "value" => $this->getSource()->getShippingAmount(),
                    "label" => __("Shipping & Handling"),
                ]
            );
        }

        if ((double)$this->getSource()->getDiscountAmount() != 0) {
            if ($this->getSource()->getDiscountDescription()) {
                $discountLabel = __("Discount (%1)", $source->getDiscountDescription());
            } else {
                $discountLabel = __("Discount");
            }
            $this->_totals["discount"] = new \Magento\Framework\DataObject(
                [
                    "code" => "discount",
                    "field" => "discount_amount",
                    "value" => $source->getDiscountAmount(),
                    "label" => $discountLabel,
                ]
            );
        }

        $this->_totals["grand_total"] = new \Magento\Framework\DataObject(
            [
                "code" => "grand_total",
                "field" => "grand_total",
                "strong" => true,
                "value" => $source->getGrandTotal(),
                "label" => __("Grand Total"),
            ]
        );

        /**
        * Base grandtotal
        */
        if ($this->getOrder()->isCurrencyDifferent()) {
            $this->_totals["base_grandtotal"] = new \Magento\Framework\DataObject(
                [
                    "code" => "base_grandtotal",
                    "value" => $this->getOrder()->formatBasePrice($source->getBaseGrandTotal()),
                    "label" => __("Grand Total to be Charged"),
                    "is_formated" => true,
                ]
            );
        }
        return $this;
    }
}
