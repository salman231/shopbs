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
namespace Webkul\GiftCard\Model;

use Magento\Checkout\Model\ConfigProviderInterface;

class ExtrafeeConfigProvider implements ConfigProviderInterface
{

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->logger = $logger;
    }
    /**
     * @return array
     */
    public function getConfig()
    {
        $ExtrafeeConfig = [];
        $ExtrafeeConfig['fee_label'] = __('Gift Card Coupon');
        $quote = $this->checkoutSession->getQuote();
        $subtotal = $quote->getSubtotal();
        $ExtrafeeConfig['custom_fee_amount'] = $quote->getFee();
        $ExtrafeeConfig['show_hide_Extrafee_block'] = true;
        $ExtrafeeConfig['show_hide_Extrafee_shipblock'] = true;
        return $ExtrafeeConfig;
    }
}
