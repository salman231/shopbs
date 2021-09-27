<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category  Mageplaza
 * @package   Mageplaza_BetterProductReviews
 * @copyright Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license   https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\BetterProductReviews\Plugin\App\Action;

use Closure;
use Magento\Customer\Model\Session;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Framework\App\RequestInterface;
use Mageplaza\BetterProductReviews\Model\Customer\Context as CustomerSessionContext;

/**
 * Class Context
 *
 * @package Mageplaza\BetterProductReviews\Plugin\App\Action
 */
class Context
{
    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var HttpContext
     */
    protected $httpContext;

    /**
     * Context constructor.
     *
     * @param Session $customerSession
     * @param HttpContext $httpContext
     */
    public function __construct(
        Session $customerSession,
        HttpContext $httpContext
    ) {
        $this->customerSession = $customerSession;
        $this->httpContext = $httpContext;
    }

    /**
     * @param ActionInterface $subject
     * @param Closure $proceed
     * @param RequestInterface $request
     *
     * @return mixed
     * @SuppressWarnings(Unused)
     */
    public function aroundDispatch(
        ActionInterface $subject,
        Closure $proceed,
        RequestInterface $request
    ) {
        $customerId = $this->customerSession->getCustomerId();
        if (!$customerId) {
            $customerId = 0;
        }

        $this->httpContext->setValue(
            CustomerSessionContext::CONTEXT_CUSTOMER_ID,
            $customerId,
            false
        );

        return $proceed($request);
    }
}
