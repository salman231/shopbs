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

class SalesQuoteRemoveItem implements ObserverInterface
{
    public function __construct(
        \Webkul\GiftCard\Helper\Data $helper
    ) {
        $this->helper = $helper;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $this->helper->clearGiftCode();
    }
}
