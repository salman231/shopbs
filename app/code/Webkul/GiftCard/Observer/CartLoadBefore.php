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

namespace Webkul\GiftCard\Observer;
 
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\App\RequestInterface;
 
class CartLoadBefore implements ObserverInterface
{
 
    /** @var \Magento\Framework\Logger\Monolog */
    protected $_logger;

    /** @var Magento\Framework\App\RequestInterface */
    protected $_request;

    /**
     * @param \Psr\Log\LoggerInterface               $loggerInterface
     * @param RequestInterface                       $requestInterface
     */
    public function __construct(
        \Psr\Log\LoggerInterface $loggerInterface,
        RequestInterface $requestInterface,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Customer\Model\Session $customerSession,
        \Webkul\GiftCard\Model\GiftUserFactory $giftUser,
        \Webkul\GiftCard\Helper\Data $dataHelper,
        \Magento\Framework\Session\SessionManagerInterface $session,
        \Magento\Checkout\Model\Cart $quote,
        \Magento\Framework\Message\ManagerInterface $messegeManager,
        \Magento\Framework\UrlInterface $url,
        \Magento\Framework\App\ResponseFactory $responseFactory
    ) {
        $this->_logger = $loggerInterface;
        $this->_request = $requestInterface;
        $this->_checkoutSession = $checkoutSession;
        $this->_customerSession = $customerSession;
        $this->_giftUser = $giftUser;
        $this->_dataHelper = $dataHelper;
        $this->_session = $session;
        $this->_quote = $quote;
        $this->_messegeManager = $messegeManager;
        $this->_url = $url;
        $this->_responseFactory = $responseFactory;
    }
    /**
     * This is the method that fires when the event runs.
     *
     * @param Observer $observer
     */

    public function execute(Observer $observer)
    {
    }
}
