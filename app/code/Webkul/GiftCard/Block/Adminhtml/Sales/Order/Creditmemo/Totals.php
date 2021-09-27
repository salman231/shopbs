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
namespace Webkul\GiftCard\Block\Adminhtml\Sales\Order\Creditmemo;

class Totals extends \Magento\Framework\View\Element\Template
{
    /**
     * Order invoice
     *
     * @var \Magento\Sales\Model\Order\Creditmemo|null
     */
    protected $_creditmemo = null;

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
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Sales\Model\Order $salesOrder,
        \Magento\Sales\Model\ResourceModel\Order\Creditmemo\CollectionFactory $creditMemo,
        \Magento\Quote\Model\QuoteFactory $quoteFactory,
        array $data = []
    ) {
        $this->request = $request;
        $this->creditMemo = $creditMemo;
        $this->quoteFactory = $quoteFactory;
        $this->_salesOrder = $salesOrder;
        parent::__construct($context, $data);
    }

    /**
     * Get data (totals) source model
     *
     * @return \Magento\Framework\DataObject
     */
    public function getSource()
    {
        return $this->getParentBlock()->getSource();
    }

    public function getCreditmemo()
    {
        return $this->getParentBlock()->getCreditmemo();
    }
    /**
     * Initialize payment fee totals
     *
     * @return $this
     */
    public function initTotals()
    {
        $this->getParentBlock();
        $this->getCreditmemo();
        $this->getSource();
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
                    'base_value' => $quote->getBasefee(),
                    'label' => __("Gift Card Coupon"),
                ]
            );
        } else {
            $orderId = $this->getCreditmemo()->getOrderId();
            
            $sl = $this->_salesOrder->load($orderId);
    
            $quote = $this->quoteFactory->create()->load($sl->getQuoteId());
            if (!$quote->getFee()) {
                return $this;
            }
            $fee = new \Magento\Framework\DataObject(
                [
                    'code' => 'fee',
                    'strong' => false,
                    'value' => $quote->getFee(),
                    'base_value' =>  $quote->getBasefee(),
                    'label' =>  __("Gift Card Coupon"),
                ]
            );
        }
        $this->getParentBlock()->addTotalBefore($fee, 'grand_total');

        return $this;
    }
}
