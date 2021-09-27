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
 
class ControllerActionPostdispatchCurrencySwitch implements ObserverInterface
{
 
    /** @var \Magento\Framework\Logger\Monolog */
    protected $_logger;

    /**
     * @param \Psr\Log\LoggerInterface               $loggerInterface
     * @param RequestInterface                       $requestInterface
     */
    public function __construct(
        \Psr\Log\LoggerInterface $loggerInterface,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Webkul\GiftCard\Helper\Data $dataHelper
    ) {
        $this->_logger = $loggerInterface;
        $this->_dataHelper = $dataHelper;
        $this->messageManager = $messageManager;
        $this->checkoutSession = $checkoutSession;
    }

    public function execute(Observer $observer)
    {
        if ($this->checkoutSession->getGiftCode()) {
            $this->_dataHelper->clearGiftCode();
            $this->messageManager->addWarning(__("Gift Card Discount Removed."));
        }
    }
}
