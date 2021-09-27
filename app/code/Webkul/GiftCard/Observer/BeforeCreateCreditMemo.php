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
use Magento\Framework\Exception\LocalizedException;
 
class BeforeCreateCreditMemo implements ObserverInterface
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
        \Magento\Framework\Message\Manager $messageManager,
        \Magento\Framework\App\ResponseFactory $responseFactory,
        \Magento\Framework\UrlInterface $url,
        \Magento\Framework\App\Response\Http $redirect
    ) {
        $this->_logger = $loggerInterface;
        $this->_request = $requestInterface;
        $this->_messageManager = $messageManager;
        $this->_responseFactory = $responseFactory;
        $this->_url = $url;
        $this->_redirect = $redirect;
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
