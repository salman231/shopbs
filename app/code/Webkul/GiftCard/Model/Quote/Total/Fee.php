<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_GiftCard
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\GiftCard\Model\Quote\Total;

use Magento\Store\Model\ScopeInterface;

class Fee extends \Magento\Quote\Model\Quote\Address\Total\AbstractTotal
{

    protected $helperData;
    protected $_priceCurrency;

    /**
     * Collect grand total address amount
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     * @return $this
     */
    protected $quoteValidator = null;

    public function __construct(
        \Magento\Quote\Model\QuoteValidator $quoteValidator,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Webkul\GiftCard\Helper\Data $helperData,
        \Psr\Log\LoggerInterface $logger
    ) {
    
        $this->_checkoutSession = $checkoutSession;
        $this->quoteValidator = $quoteValidator;
        $this->_priceCurrency = $priceCurrency;
        $this->helperData = $helperData;
        $this->logger = $logger;
    }

    public function collect(
        \Magento\Quote\Model\Quote $quote,
        \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment,
        \Magento\Quote\Model\Quote\Address\Total $total
    ) {
    
        parent::collect($quote, $shippingAssignment, $total);
        if (empty($shippingAssignment->getItems())) {
            return $this;
        }
        if ($this->_checkoutSession->getGift()) {
            $fee = $this->_checkoutSession->getAmount();
            $rates = $this->helperData->getCurrentCurrencyRate();
            $basefee=$fee/$rates;
            $giftCode = $this->_checkoutSession->getGiftCode();
            $total->setTotalAmount('fee', $fee);
            $total->setBaseTotalAmount('fee', $basefee);
            $total->setGrandTotalAmount('fee', $fee);
            $total->setBaseGrandTotalAmount('fee', $basefee);
            $total->setFee($fee);
            $quote->setFee($fee);
            $quote->setBasefee($basefee);
            $quote->setGiftCode($giftCode);
        }
        return $this;
    }

    /**
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     * @return array
     */
    public function fetch(\Magento\Quote\Model\Quote $quote, \Magento\Quote\Model\Quote\Address\Total $total)
    {
        $subtotal = $quote->getSubtotal();
        $fee = $quote->getFee();
        $giftCode = $this->_checkoutSession->getGiftCode();
        $result = [];
        if ($this->_checkoutSession->getGift()) {
            $result = [
                'code' => 'fee',
                'title' =>  __($giftCode),
                'value' => $fee
            ];
        }
        
        return $result;
    }

    /**
     * Get Subtotal label
     *
     * @return \Magento\Framework\Phrase
     */
    public function getLabel()
    {
        return __('Extra Fee');
    }

    /**
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     */
    protected function clearValues(\Magento\Quote\Model\Quote\Address\Total $total)
    {
        $total->setTotalAmount('subtotal', 0);
        $total->setBaseTotalAmount('subtotal', 0);
        $total->setTotalAmount('tax', 0);
        $total->setBaseTotalAmount('tax', 0);
        $total->setTotalAmount('discount_tax_compensation', 0);
        $total->setBaseTotalAmount('discount_tax_compensation', 0);
        $total->setTotalAmount('shipping_discount_tax_compensation', 0);
        $total->setBaseTotalAmount('shipping_discount_tax_compensation', 0);
        $total->setSubtotalInclTax(0);
        $total->setBaseSubtotalInclTax(0);
    }
}
