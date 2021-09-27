<?php
/**
 * Dart Productkeys Deliver Controller
 *
 * @package        Dart_Productkeys
 *
 */
namespace Dart\Productkeys\Controller\Adminhtml\Productkeys;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Backend\Model\UrlInterface;
use Magento\Email\Model\ResourceModel\Template\CollectionFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Sales\Model\Order\Item;
use Magento\Catalog\Model\ProductFactory;
use Dart\Productkeys\Helper\Data;

class Deliverkeys extends Action
{
    protected $templateId;

    public function __construct(
        Context $context,
        ScopeConfigInterface $scopeConfig,
        CollectionFactory $emailTemplates,
        UrlInterface $backendUrl,
        StoreManagerInterface $storeManager,
        Item $orderItem,
        ProductFactory $productFactory,
        Data $helperData,
        TransportBuilder $transportBuilder
    ) {
        parent::__construct($context);
        $this->scopeConfig = $scopeConfig;
        $this->emailTemplates = $emailTemplates;
        $this->backendUrl = $backendUrl;
        $this->storeManager = $storeManager;
        $this->orderItem = $orderItem;
        $this->productFactory = $productFactory;
        $this->helperData = $helperData;
        $this->transportBuilder = $transportBuilder;
    }

    public function execute()
    {
        $item_ids = $this->getRequest()->getParams();

        foreach (explode(",", $item_ids["productkey_items"]) as $itemId) {
            $order_item = $this->orderItem->load($itemId);
            $productkeys = $order_item->getProductKeys();
            if (empty($productkeys)) {
                $message = __("Email for %1 not sent. No message or Keys issued.", $order_item->getName());
                $this->messageManager->addError($message);
            } else {
                $keys_html = '<div class="prdkey_items"><span class="prdkey_product">'.$order_item->getName().'</span>';
                $key_type = $order_item->getProductKeyType();
                
                $product = $this->productFactory->create()->load($order_item->getProductId());

                if ($product->getProductkeyOverwritegnrlconfig()) {
                    $templateId = $product->getProductkeyEmailTemplate();
                } else {
                    $templateId = $this->helperData->getGeneralConfig('productkeys_email_template');
                }
                if (!is_numeric($templateId) || $templateId == 0) {
                    $templates = $this->emailTemplates->create()
                            ->addFieldToFilter('template_code', 'Productkey Delivery');
                    foreach ($templates as $template) {
                        $templateId = $template->getTemplateId();
                    }
                }

                foreach (explode(',', $productkeys) as $keys) {
                    $keys_html .= '<br /><span class="prdkey_type">'.$key_type.': </span>
                            <span class="prdkey_code"><strong>'.$keys.'</strong></span>';
                }
                $keys_html .= '</div>';

                $order = $order_item->getOrder();
                $emailVars = [
                    'storeGroupName' => $order->getStoreGroupName(),
                    'name' => $order->getBillingAddress()->getName(),
                    'keytype' => $key_type,
                    'itemshtml' => $keys_html,
                    'order' => $order
                ];
                $templateOptions = [
                    'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                    'store' => $this->storeManager->getStore()->getId()
                ];

                $sender = [
                    'name' => $this->scopeConfig
                            ->getValue('trans_email/ident_sales/name', ScopeInterface::SCOPE_STORE),
                    'email' => $this->scopeConfig
                            ->getValue('trans_email/ident_sales/email', ScopeInterface::SCOPE_STORE)
                ];

                try {
                    $mesContent = $this->transportBuilder->setTemplateIdentifier($templateId)
                            ->setTemplateOptions($templateOptions)
                            ->setTemplateVars($emailVars)
                            ->setFrom($sender)
                            ->addTo($order_item->getOrder()->getCustomerEmail())
                            ->getTransport();
                    $mesContent->sendMessage();
                    $txt = "An email containing %1(s) has been sent to %2.";
                    $message = __($txt, $key_type, $order_item->getOrder()->getCustomerEmail());
                    $this->messageManager->addSuccess($message);
                } catch (\Exception $e) {
                    $this->messageManager->addError($e->getMessage());
                }
            }
            $orderPage = $this->backendUrl->getUrl('sales/order/view', ['order_id' => $item_ids['order_id']]);
            $this->_redirect($orderPage);
        }
    }
}
