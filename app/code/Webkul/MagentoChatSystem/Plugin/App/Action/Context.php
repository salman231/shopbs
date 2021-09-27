<?php
/**
 * Webkul Software.
 *
 * @category Webkul
 * @package Webkul_MagentoChatSystem
 * @author Webkul
 * @copyright Copyright (c) WebkulSoftware Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 *
 */
namespace Webkul\MagentoChatSystem\Plugin\App\Action;

class Context
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;
 
    /**
     * @var \Magento\Framework\App\Http\Context
     */
    protected $httpContext;
 
    /**
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\App\Http\Context $httpContext
     */
    public function __construct(
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\App\Http\Context $httpContext
    ) {
        $this->customerSession = $customerSession;
        $this->httpContext = $httpContext;
    }
 
    /**
     * @param \Magento\Framework\App\ActionInterface $subject
     * @param callable $proceed
     * @param \Magento\Framework\App\RequestInterface $request
     * @return mixed
     */
    public function aroundDispatch(
        \Magento\Framework\App\ActionInterface $subject,
        \Closure $proceed,
        \Magento\Framework\App\RequestInterface $request
    ) {
        
        $customerId = $this->customerSession->getCustomerId();
        if (!$customerId) {
            $customerId = 0;
        }

        $this->httpContext->setValue(
            'chat_customer_id',
            $customerId,
            false
        );
 
        return $proceed($request);
    }
}
