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

class AfterProductSave implements ObserverInterface
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
        \Magento\Catalog\Api\Data\ProductCustomOptionInterfaceFactory $productCustom,
        RequestInterface $requestInterface
    ) {
        $this->productCustom = $productCustom;
        $this->_logger = $loggerInterface;
        $this->_request = $requestInterface;
    }

    /**
     * This is the method that fires when the event runs.
     *
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $product = $observer->getEvent()->getProduct();
        if ($product->getTypeId() == 'giftcard') {
            $hasOpt = (int)$product->getHasOptions();
            if (!$product->getHasOptions() && ($hasOpt==0)) {
                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                $emailCustomOption = $this->productCustom->create();
                $messageCustomOption = $this->productCustom->create();
                $emailCustomOption->setTitle('Email To')
                             ->setType('field')
                             ->setIsRequire(true)
                             ->setSortOrder(0)
                             ->setPrice(0)
                             ->setPriceType('percent')
                             ->setIsDelete(0)
                             ->setProductSku($product->getSku());
                $customOptions[] = $emailCustomOption;
                $messageCustomOption->setTitle('Message')
                             ->setType('area')
                             ->setIsRequire(true)
                             ->setSortOrder(0)
                             ->setPrice(0)
                             ->setPriceType('percent')
                             ->setMaxCharacters(160)
                             ->setIsDelete(0)
                             ->setProductSku($product->getSku());
                $customOptions[] = $messageCustomOption;
                $product->setOptions($customOptions)->save();
            }
        }
    }
}
