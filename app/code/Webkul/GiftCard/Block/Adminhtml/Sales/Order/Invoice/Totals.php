<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_GiftCard
 * @author    Webkul Software Private Limited
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\GiftCard\Block\Adminhtml\Sales\Order\Invoice;

class Totals extends \Magento\Framework\View\Element\Template
{

    /**
     * Order invoice
     *
     * @var \Magento\Sales\Model\Order\Invoice|null
     */
    protected $_invoice = null;

    /**
     * @var \Magento\Framework\DataObject
     */
    protected $_source;

    /**
     * OrderFee constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Sales\Model\OrderFactory $salesOrder,
        \Magento\Quote\Model\QuoteFactory $quoteFactory,
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Sales\Model\OrderFactory $order,
        array $data = []
    ) {
     
        $this->quoteFactory = $quoteFactory;
        $this->_salesOrder = $salesOrder;
        $this->order = $order;
        parent::__construct($context, $data);
    }

    public function getSource()
    {
        return $this->_source;
    }
 
    public function displayFullSummary()
    {
        return true;
    }
    public function initTotals()
    {
        $parent = $this->getParentBlock();
        $this->_order = $parent->getOrder();
        $this->_source = $parent->getSource();
        $orderId = $this->getInvoice()->getOrderId();
        $data = $this->getInvoiceDetails($orderId);
        $sl = $this->_salesOrder->create()->load($orderId);
        $quote = $this->quoteFactory->create()->load($sl->getQuoteId());
        if (!$quote->getFee()) {
            return $this;
        }
        if ($data == '') {
            $total = new \Magento\Framework\DataObject(
                [
                    'code' => 'fee',
                    'value' => $quote->getFee(),
                    'base_value' =>  $quote->getBasefee(),
                    'label' => __("Gift Card Coupon"),
                ]
            );

            $this->getParentBlock()->addTotalBefore($total, 'grand_total');
        }
        return $this;
    }

    public function getInvoiceDetails($order_id)
    {
        $invoice_id = '';
        $orderdetails = $this->order->create()->load($order_id);
        $orderdetails->getGrandTotal();
        foreach ($orderdetails->getInvoiceCollection() as $invoice) {
               $invoice_id = $invoice->getIncrementId();
        }
             return $invoice_id;
    }
    /**
     * Get order store object
     *
     * @return \Magento\Store\Model\Store
     */
    public function getStore()
    {
        return $this->_order->getStore();
    }
    /**
     * @return Order
     */
    public function getOrder()
    {
        return $this->_order;
    }
    /**
     * @return array
     */
    public function getLabelProperties()
    {
        return $this->getParentBlock()->getLabelProperties();
    }
    /**
     * @return array
     */
    public function getValueProperties()
    {
        return $this->getParentBlock()->getValueProperties();
    }
}
