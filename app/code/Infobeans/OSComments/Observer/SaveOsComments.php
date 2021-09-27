<?php

namespace Infobeans\OSComments\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

class SaveOsComments implements ObserverInterface
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    // @codingStandardsIgnoreLine
    protected $quoteRepository;

    /**
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     */
    public function __construct(
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
    ) {
        $this->quoteRepository = $quoteRepository;
    }

    /**
     * @param EventObserver $observer
     * @return \Infobeans\OSComments\Observer\SaveOsComments
     */
    public function execute(EventObserver $observer)
    {
        
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $logger = $objectManager->get('\Psr\Log\LoggerInterface');
        
        $order = $observer->getOrder();
        $quote = $this->quoteRepository->get($order->getQuoteId());
        $logger->info(print_r($quote->getDeliveryComment(),true));
        // exit;
        $order->setDeliveryComment(htmlspecialchars($quote->getDeliveryComment()));
        return $this;
    }
}
