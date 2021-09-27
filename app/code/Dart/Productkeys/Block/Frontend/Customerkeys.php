<?php
/**
 * Dart Productkeys Customer Dashboard Block.
 * @package   Dart_Productkeys
 *
 */
namespace Dart\Productkeys\Block\Frontend;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Customer\Model\Session;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;

class Customerkeys extends Template
{
    public function __construct(
        Context $context,
        Session $customerSession,
        CollectionFactory $orderCollection,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->customerSession = $customerSession;
        $this->orderCollection = $orderCollection;
    }
    
    public function getCustomerOrders()
    {
        $customerId = $this->customerSession->getCustomerId();
        $customerOrders = $this->orderCollection->create()
                        ->addFieldToSelect('*')
                        ->addFieldToFilter('customer_id', $customerId)
                        ->setOrder('created_at', 'desc');
        return $customerOrders;
    }
}
