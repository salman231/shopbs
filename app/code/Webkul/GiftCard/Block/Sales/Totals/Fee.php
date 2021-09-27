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
namespace Webkul\GiftCard\Block\Sales\Totals;

class Fee extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Webkul\GiftCard\Helper\Data
     */
    protected $_dataHelper;

    /**
     * @var Order
     */
    protected $_order;

    /**
     * @var \Magento\Framework\DataObject
     */
    protected $_source;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Sales\Model\OrderFactory $salesOrder,
        \Magento\Quote\Model\QuoteFactory $quoteFactory,
        \Magento\Sales\Model\ResourceModel\Order\Creditmemo\CollectionFactory $creditMemo,
        \Magento\Sales\Model\ResourceModel\Order\Invoice\CollectionFactory $invoice,
        \Magento\Framework\App\Request\Http $request,
        array $data = []
    ) {
    
        $this->invoice = $invoice;
        $this->creditMemo = $creditMemo;
        $this->request = $request;
        $this->quoteFactory = $quoteFactory;
        $this->_salesOrder = $salesOrder;
        parent::__construct($context, $data);
    }

    /**
     * Check if we nedd display full tax total info
     *
     * @return bool
     */
    public function displayFullSummary()
    {
        return true;
    }

    /**
     * Get data (totals) source model
     *
     * @return \Magento\Framework\DataObject
     */
    public function getSource()
    {
        return $this->_source;
    }

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

    /**
     * @return $this
     */
    public function initTotals()
    {
        $parent = $this->getParentBlock();
        $this->_order = $parent->getOrder();
        $this->_source = $parent->getSource();
        if ($this->request->getParam('creditmemo_id')) {
            $creditMemoId = $this->request->getParam('creditmemo_id');
            $creditData = $this->creditMemo->create()->addFieldToFilter('entity_id', $creditMemoId)->getData();
            $sl = $this->_salesOrder->load($creditData[0]['order_id']);
            $quote = $this->quoteFactory->create()->load($sl->getQuoteId());
            $fee = new \Magento\Framework\DataObject(
                [
                    'code' => 'fee',
                    'strong' => false,
                    'value' => $creditData[0]['fee'],
                    'base_value' =>  $quote->getBasefee(),
                    'label' => __("Gift Card Coupon"),
                ]
            );
        } elseif ($this->request->getParam('invoice_id')) {
            $invoiceData = $this->invoice->create()
            ->addFieldToFilter('entity_id', $this->request->getParam('invoice_id'))->getData();
            $sl = $this->_salesOrder->load($invoiceData[0]['order_id']);
            $quote = $this->quoteFactory->create()->load($sl->getQuoteId());
            $fee = new \Magento\Framework\DataObject(
                [
                    'code' => 'fee',
                    'strong' => false,
                    'value' => $invoiceData[0]['fee'],
                    'base_value' =>  $quote->getBasefee(),
                    'label' => __("Gift Card Coupon"),
                ]
            );
        } elseif ($this->request->getParam('order_id')) {
            $sl = $this->_salesOrder->create()->load($this->request->getParam('order_id'));
            $quote = $this->quoteFactory->create()->load($sl->getQuoteId());
            $fee = new \Magento\Framework\DataObject(
                [
                    'code' => 'fee',
                    'strong' => false,
                    'value' => $quote->getFee(),
                    'base_value' =>  $quote->getBasefee(),
                    'label' => __("Gift Card Coupon"),
                ]
            );
        } elseif ($this->_order->getId()) {
            $sl = $this->_salesOrder->create()->load($this->_order->getId());
            $quote = $this->quoteFactory->create()->load($sl->getQuoteId());
            $fee = new \Magento\Framework\DataObject(
                [
                    'code' => 'fee',
                    'strong' => false,
                    'value' => $quote->getFee(),
                    'base_value' =>  $quote->getBasefee(),
                    'label' => __("Gift Card Coupon"),
                ]
            );
        }
        if (isset($fee)) {
            $parent->addTotal($fee, 'fee');
        }
        return $this;
    }
}
