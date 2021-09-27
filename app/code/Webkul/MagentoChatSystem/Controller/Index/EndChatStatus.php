<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MagentoChatSystem
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MagentoChatSystem\Controller\Index;

use Magento\Framework\Exception\NotFoundException;
use Webkul\MagentoChatSystem\Model\ResourceModel\CustomerData\CollectionFactory;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class EndChatStatus extends \Magento\Framework\App\Action\Action
{
    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param CollectionFactory $dataCollection,
     * @param \Magento\Customer\Model\SessionFactory $customerSessionFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        CollectionFactory $dataCollection,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Customer\Model\SessionFactory $customerSessionFactory
    ) {
        parent::__construct($context);
        $this->_dataCollection = $dataCollection;
        $this->customerSessionFactory = $customerSessionFactory;
        $this->resultJsonFactory = $resultJsonFactory;
    }

    /**
     * Customer view file action
     *
     * @return \Magento\Framework\Controller\ResultInterface|void
     * @throws NotFoundException
     *
     * @SuppressWarnings(PHPMD.ExitExpression)
     */
    public function execute()
    {
        $customerId = $this->customerSessionFactory->create()->getCustomer()->getId();
        $customerData = [];
        if ($customerId) {
            $chatCustomerCollection = $this->_dataCollection->create()
                ->addFieldToFilter('customer_id', ['eq' => $customerId]);
            $chatCustomerData = $chatCustomerCollection->getFirstItem();
            $paramData = $this->getRequest()->getParams();
            if (isset($paramData['status'])) {
                $chatCustomerData->setEndchat(0)->save();
            }
            $customerData['endchat_status'] = $chatCustomerData->getEndchat();
        }
        $result = $this->resultJsonFactory->create();
        return $result->setData($customerData);
    }
}
