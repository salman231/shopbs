<?php
/**
 * Dart Productkeys Invoice Observer
 *
 * @package        Dart_Productkeys
 *
 */
namespace Dart\Productkeys\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Catalog\Model\ProductFactory;
use Magento\Email\Model\ResourceModel\Template\CollectionFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Mail\Template\TransportBuilder;
use Dart\Productkeys\Controller\Adminhtml\Productkeys\Generatekeys;
use Dart\Productkeys\Helper\Data;

class EmailProductkeys implements ObserverInterface
{
    private $issueOnInvoice;
    private $sendEmail;
    private $templateId;
    private $no_keys;

    public function __construct(
        ProductFactory $productFactory,
        CollectionFactory $emailTemplates,
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig,
        TransportBuilder $transportBuilder,
        Generatekeys $generateKeys,
        Data $helperData
    ) {
        $this->productFactory = $productFactory;
        $this->emailTemplates = $emailTemplates;
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        $this->transportBuilder = $transportBuilder;
        $this->generateKeys = $generateKeys;
        $this->helperData = $helperData;
    }

    public function execute(Observer $observer)
    {
        $order = $observer->getEvent()->getInvoice()->getOrder();
        $orderIncId = $order->getIncrementId();

        foreach ($order->getAllItems() as $item) {
            if (!$item->getParentItemId()) {
                $product = $this->productFactory->create()->load($item->getProductId());
                $storeId = $item->getStoreId();
                if ($product->getProductkeyOverwritegnrlconfig()) {
                    $issueOnInvoice = $product->getProductkeyIssueInvoice();
                    $sendEmail = $product->getProductkeySendEmail();
                    $templateId = $product->getProductkeyEmailTemplate();
                    $no_keys = $product->getProductkeyNotAvailable();
                    $key_type = $product->getProductkeyType();
                } else {
                    $issueOnInvoice = $this->helperData->getGeneralConfig('issue_invoice');
                    $sendEmail = $this->helperData->getGeneralConfig('productkeys_send_email');
                    $templateId = $this->helperData->getGeneralConfig('productkeys_email_template');
                    $no_keys = $this->helperData->getGeneralConfig('productkeys_not_available');
                    $key_type = $this->helperData->getGeneralConfig('productkeys_type');
                }

                if ($issueOnInvoice) {
                    $productkey = [];
                    $productkeyIds = [];
                    $keypool = $product->getProductkeyPool();
                    if (empty($keypool)) {
                        $keypool = $item->getSku();
                    }

                    $keys_html = '<div class="prdkey_items"><span class="prdkey_product">'
                                    .$product->getName().'</span>';
                    $collection = $this->generateKeys->fetchProductKeys($orderIncId, $keypool);
                    $overall_qty = (int) $item->getQtyOrdered();
                    $issuedKeysCount = 0;
                    for ($i=0; $i<$overall_qty; $i++) {
                        if (!array_key_exists($i, $collection) || count($collection) < $order->getTotalQtyOrdered()) {
                            $productkeyvalues = $this->generateKeys
                            ->saveOrderToProductkeys($orderIncId, $keypool, $product, $storeId);
                            if ($productkeyvalues['productkey_availability'] != 'Keys Issued') {
                                if ($product->getTypeId() == 'configurable') {
                                    $product = $this->productFactory->create()
                                            ->loadByAttribute('sku', $item->getSku());
                                    $keypool = $product->getProductkeyPool();
                                    if (empty($keypool)) {
                                        $keypool = $product->getSku();
                                    }
                                    $productkeyvalues = $this->generateKeys
                                    ->saveOrderToProductkeys($orderIncId, $keypool, $product, $storeId);
                                }
                                $inCollection = $productkeyvalues['productkey_availability'];
                                if ($inCollection != "No Keys") {
                                    return '';
                                }
                            }
                            $productkeyIds[$i] = $productkeyvalues['productkey_id'];
                            $productkey[$i] = $productkeyvalues['product_key'];
                        } else {
                            $productkeyIds[$i] = $collection[$i]['id'];
                            $productkey[$i] = $collection[$i]['product_key'];
                        }
                        
                        if (empty($productkey[$i])) {
                            if (empty($no_keys)) {
                                $no_keys = 'Oops! No Productkey Available right now. Please call or email.';
                            }
                            $productkey[$i] = $no_keys;
                        } else {
                            $issuedKeysCount++;
                        }

                        if (empty($key_type)) {
                            $key_type = 'Productkey';
                        }

                        if ($i+1 == $overall_qty) {
                            $item->setProductKeyType($key_type);
                            $item->setProductKeys(implode(",", $productkey));
                            $item->setProductKeyIds(implode(",", $productkeyIds));
                            $item->setProductKeysIssued($issuedKeysCount);
                            $item->setProductKeyPool($keypool);
                        }

                        $keys_html .= '<br /><span class="prdkey_type">'.$key_type.':</span> <span class="prdkey_code">
                            <strong>'.$productkey[$i].'</strong></span>';
                    }
                    $keys_html .= '</div>';

                    if ($sendEmail) {
                        if (!is_numeric($templateId) || $templateId == 0) {
                            $templates = $this->emailTemplates->create()
                                    ->addFieldToFilter('template_code', 'Productkey Delivery');
                            foreach ($templates as $template) {
                                $templateId = $template->getTemplateId();
                            }
                        }
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
                            $transport = $this->transportBuilder->setTemplateIdentifier($templateId)
                                        ->setTemplateOptions($templateOptions)
                                        ->setTemplateVars($emailVars)
                                        ->setFrom($sender)
                                        ->addTo($order->getCustomerEmail())
                                        ->getTransport();
                            $transport->sendMessage();
                        } catch (\Exception $e) {
                            return $e->getMessage();
                        }
                    }
                }
            }
        }
    }
}
