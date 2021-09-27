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
namespace Webkul\GiftCard\Model\Creditmemo\Total;

use Magento\Sales\Model\Order\Creditmemo\Total\AbstractTotal;

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
     * @param \Magento\Sales\Model\Order\Creditmemo $creditmemo
     * @return $this
     */
    public function collect(\Magento\Sales\Model\Order\Creditmemo $creditmemo)
    {
        $orderId = $creditmemo->getOrder()->getId();
        $sl = $this->_salesOrder->load($orderId);
        $quote = $this->quoteFactory->create()->load($sl->getQuoteId());
        $creditmemo->setFee(0);
        
        $amount =  $quote->getFee();
        $creditmemo->setFee($amount);

        $creditmemo->setGrandTotal($creditmemo->getGrandTotal() +  $quote->getFee());
        $creditmemo->setBaseGrandTotal($creditmemo->getBaseGrandTotal() +  $quote->getBasefee());

        return $this;
    }
}
