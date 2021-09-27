<?php

/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Ranosys\CancelOrder\Controller\Order;

use Magento\Sales\Controller\OrderInterface;
use Magento\Framework\App\Action\Context;
use Magento\Sales\Controller\AbstractController\OrderLoaderInterface;
use Magento\Framework\Registry;
use Magento\Checkout\Model\Session;

/**
 * Controller for cancel order
 */
class Cancel extends \Magento\Framework\App\Action\Action implements OrderInterface
{

    /**
     * @var \Magento\Sales\Api\OrderManagementInterface
     */
    protected $order;

    /**
     * @var \Magento\Sales\Controller\AbstractController\OrderLoaderInterface
     */
    protected $orderLoader;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;
    
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $session;

    /**
     * Cancel constructor.
     *
     * @param \Magento\Sales\Api\OrderManagementInterface $orderManagementInterface
     * @param \Magento\Checkout\Model\Session             $session
     * @param Context                                     $context
     */
    public function __construct(
        \Magento\Sales\Api\OrderManagementInterface $orderManagementInterface,
        OrderLoaderInterface $orderLoader,
        \Magento\Checkout\Model\Session $session,
        \Magento\Customer\Model\Session $customerSession,
        Registry $registry,
        Context $context
    ) {
        $this->order = $orderManagementInterface;
        $this->session = $session;
        $this->orderLoader = $orderLoader;
        $this->registry = $registry;
        $this->customerSession = $customerSession;
        $this->redirect = $context->getRedirect();
        parent::__construct($context);
    }

    /**
     * to cancel an order
     *
     * @return void
     */
    public function execute()
    {
        $result = $this->orderLoader->load($this->_request);
        if ($result instanceof \Magento\Framework\Controller\ResultInterface) {
            return $result;
        }
        $order = $this->registry->registry('current_order');
        $resultRedirect = $this->resultRedirectFactory->create();

        try {
            if ($order->getStatus() == 'pending') {
                $this->order->cancel($order->getId());
                $post = $this->getRequest()->getPostValue();
                if (!empty($post)) {
                    $comment = $post['comment'];
                    $order->addStatusToHistory('canceled', $comment);
                    $order->save();
                }
                $this->messageManager->addSuccess(__('The order has been Canceled successfully.'));
            } else {
                $this->messageManager->addError(
                    __('We can\'t process your request right now. Please Try after some time.')
                );
            }
            $customerId = $this->customerSession->getId();
            if($customerId) {
                $this->customerSession->logout()
                     ->setBeforeAuthUrl($this->redirect->getRefererUrl())
                     ->setLastCustomerId($customerId);
                return "Membership canceled. Customer logout successfully";
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            if ($this->session->getUseNotice(true)) {
                $this->messageManager->addNotice($e->getMessage());
            } else {
                $this->messageManager->addError($e->getMessage());
            }
        }
        return $resultRedirect->setPath('*/*/history');
    }
}
