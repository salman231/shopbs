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
namespace Webkul\GiftCard\Model\Invoice\Total;

use Magento\Sales\Model\Order\Invoice\Total\AbstractTotal;

class Fee extends AbstractTotal
{
   
    public function __construct(
        \Magento\Sales\Model\Order $salesOrder,
        \Magento\Quote\Model\QuoteFactory $quoteFactory
    ) {
       
        $this->quoteFactory = $quoteFactory;
        $this->_salesOrder = $salesOrder;
    }
    /**
     * @param \Magento\Sales\Model\Order\Invoice $invoice
     * @return $this
     */
    public function collect(\Magento\Sales\Model\Order\Invoice $invoice)
    {
        $orderId = $invoice->getOrder()->getId();
        if (!empty($orderId)) {
            $sl = $this->_salesOrder->load($orderId);
            $quote = $this->quoteFactory->create()->load($sl->getQuoteId());
            $invoice->setFee($quote->getFee());
            if ($invoice->getOrder()->getSubtotalInvoiced()) {
                $invoice->setGrandTotal($invoice->getGrandTotal());
                $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal());
            } else {
                $invoice->setGrandTotal($invoice->getGrandTotal() + $quote->getFee());
                $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal() + $quote->getBasefee());
            }
        }

        return $this;
    }
}
