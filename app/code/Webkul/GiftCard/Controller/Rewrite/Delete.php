<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_GiftCard
 * @author    Webkul Software Private Limited
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\GiftCard\Controller\Rewrite;

use Magento\Checkout\Model\Cart as CustomerCart;

class Delete extends \Magento\Checkout\Controller\Cart\Delete
{

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        CustomerCart $cart,
        \Magento\Framework\UrlInterface $uri,
        \Webkul\GiftCard\Helper\Data $helper
    ) {
        $this->uri = $uri;
        $this->helper = $helper;
        $this->_formKeyValidator = $formKeyValidator;
        $this->_scopeConfig = $scopeConfig;
        $this->_checkoutSession = $checkoutSession;
        $this->_storeManager = $storeManager;
        $this->cart = $cart;
        parent::__construct($context, $scopeConfig, $checkoutSession, $storeManager, $formKeyValidator, $cart);
    }
    /**
     * Delete shopping cart item action
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        if (!$this->_formKeyValidator->validate($this->getRequest())) {
            return $this->resultRedirectFactory->create()->setPath('*/*/');
        }

        $id = (int)$this->getRequest()->getParam('id');
        if ($id) {
            try {
                $this->cart->removeItem($id)->save();
                $this->helper->giftAmountManage();
            } catch (\Exception $e) {
                $this->messageManager->addError(__('We can\'t remove the item.'));
            }
        }
        $defaultUrl = $this->uri->getUrl('*/*');
        return $this->resultRedirectFactory->create()->setUrl($this->_redirect->getRedirectUrl($defaultUrl));
    }
}
