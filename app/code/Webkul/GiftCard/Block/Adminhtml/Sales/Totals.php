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
namespace Webkul\GiftCard\Block\Adminhtml\Sales;

class Totals extends \Magento\Framework\View\Element\Template
{

    /**
     * @var \Magecomp\Extrafee\Helper\Data
     */
    protected $_dataHelper;
    /**
     * @var \Magento\Directory\Model\Currency
     */
    protected $_currency;

    public function __construct(
        \Magento\Sales\Model\OrderFactory $salesOrder,
        \Magento\Quote\Model\QuoteFactory $quoteFactory,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Directory\Model\Currency $currency,
        array $data = []
    ) {
        $this->request = $request;
        $this->quoteFactory = $quoteFactory;
        $this->_salesOrder = $salesOrder;
        parent::__construct($context, $data);
        $this->_currency = $currency;
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
        $sl = $this->_salesOrder->create()->load($this->request->getParam('order_id'));
        $quote = $this->quoteFactory->create()->load($sl->getQuoteId());
        if (!$quote->getFee()) {
            return $this;
        }
       
        $total = new \Magento\Framework\DataObject(
            [
                'code' => 'fee',
                'value' =>  $quote->getFee(),
                'base_value' =>  $quote->getBasefee(),
                'label' =>__("Gift Card Coupon"),
            ]
        );
        $this->getParentBlock()->addTotalBefore($total, 'grand_total');

        return $this;
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
