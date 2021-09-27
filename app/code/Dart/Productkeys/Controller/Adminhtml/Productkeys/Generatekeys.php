<?php
/**
 * Dart Productkeys Generate Controller
 *
 * @package        Dart_Productkeys
 *
 */
namespace Dart\Productkeys\Controller\Adminhtml\Productkeys;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Backend\Model\UrlInterface;
use Magento\Catalog\Model\ProductFactory;
use Dart\Productkeys\Model\Productkeys;
use Dart\Productkeys\Helper\Data;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Item;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Email\Model\ResourceModel\Template\CollectionFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Generatekeys extends Action
{
    /* Declaring variables */
    private $keyType;
    private $sendLowWarning;
    private $warninglvl_default;
    private $send_warningTo;
    private $productkey_type;
    private $templateId;

    public function __construct(
        Context $context,
        TimezoneInterface $dateTime,
        ProductFactory $productFactory,
        Productkeys $modelFactory,
        Data $helperData,
        Order $orderFactory,
        Item $orderItem,
        UrlInterface $backendUrl,
        TransportBuilder $transportBuilder,
        StoreManagerInterface $storeManager,
        CollectionFactory $emailTemplates,
        ScopeConfigInterface $scopeConfig
    ) {
        parent::__construct($context);
        $this->dateTime = $dateTime;
        $this->productFactory = $productFactory;
        $this->modelFactory = $modelFactory;
        $this->helperData = $helperData;
        $this->orderFactory = $orderFactory;
        $this->orderItem = $orderItem;
        $this->backendUrl = $backendUrl;
        $this->transportBuilder = $transportBuilder;
        $this->storeManager = $storeManager;
        $this->emailTemplates = $emailTemplates;
        $this->scopeConfig = $scopeConfig;
    }

    public function execute()
    {
        $item_ids = $this->getRequest()->getParams();
        $orderinc_id = $item_ids['orderinc_id'];
        foreach (explode(',', $item_ids['productkey_items']) as $itemId) {
            $order_item = $this->orderItem->load($itemId);
            $storeId = $order_item->getStoreId();
            $_product = $this->productFactory->create()->load($order_item->getProductId());
            $keypool = $_product->getProductkeyPool();
            $qty = (int) $order_item->getQtyOrdered();
            if (empty($keypool)) {
                $keypool = $_product->getSku();
            }
            $issuedKeys = $this->fetchProductKeys($orderinc_id, $keypool);
            $issuedkeysToOrder = (int) $this->fetchKeysInOrder($item_ids['order_id'], $keypool);

            if ($_product->getTypeId() == 'configurable' && count($issuedKeys) < $qty && $issuedkeysToOrder < $qty) {
                $config_product = $this->productFactory->create()->loadByAttribute('sku', $order_item->getSku());
                $keypool_config = $config_product->getProductkeyPool();
                if (empty($keypool_config)) {
                    $keypool_config = $config_product->getSku();
                }
                $issuedKeys = $this->fetchProductKeys($orderinc_id, $keypool_config);
                $issuedkeysToOrder = (int) $this->fetchKeysInOrder($item_ids['order_id'], $keypool_config);
            }

            if (count($issuedKeys) >= $qty && $issuedkeysToOrder >= $qty) {
                $message = __('All keys have already been issued for %1.', $_product->getName());
                $this->messageManager->addNotice($message);
            } else {
                $issued_productkeys = [];
                $issued_productkeyIds = [];
                $show_mes = true;
                $productkey_stat = false;
                for ($i=0; $i<$qty; $i++) {
                    $issuedKeys = $this->fetchProductKeys($orderinc_id, $keypool);
                    if (count($issuedKeys) != $qty) {
                        $productkey_stat = $this->saveOrderToProductkeys($orderinc_id, $keypool, $_product, $storeId);
                        if ($productkey_stat['productkey_availability'] != 'Keys Issued') {
                            $inCollection = $productkey_stat['productkey_availability'];
                            if ($_product->getTypeId() == 'configurable') {
                                $show_mes = false;
                                $_product = $this->productFactory->create()
                                        ->loadByAttribute('sku', $order_item->getSku());
                                $keypool = $_product->getProductkeyPool();
                                if (empty($keypool)) {
                                    $keypool = $_product->getSku();
                                }
                                $Key_stat = $this->saveOrderToProductkeys($orderinc_id, $keypool, $_product, $storeId);
                                if ($Key_stat['productkey_availability'] != 'Keys Issued') {
                                    $inCollection = $productkey_stat['productkey_availability'];
                                    $show_mes = true;
                                }
                            }

                            $message = __('Ran out of keys for %1', $_product->getName());
                            if ($inCollection != "No Keys") {
                                $message = __($inCollection, $_product->getName());
                            }

                            if ($show_mes) {
                                $this->messageManager->addError($message);
                                break 2;
                            }
                        }
                    }

                    $savedKeys = $this->fetchProductKeys($orderinc_id, $keypool);
                    $savedKeysCount = count($savedKeys);
                    if ($savedKeysCount) {
                        $issued_productkeys[$i] = $savedKeys[$i]['product_key'];
                        $issued_productkeyIds[$i] = $savedKeys[$i]['id'];
                    }

                    if ($_product->getProductkeyOverwritegnrlconfig()) {
                        $keyType = $_product->getProductkeyType();
                    } else {
                        $keyType = $this->helperData->getGeneralConfig('productkeys_type');
                    }

                    $txt = 'A total of %1 product(s) Qty that are linked/mapped with Keypool "%2" has been updated.';
                    if ($savedKeysCount == $qty) {
                        if ($productkey_stat) {
                            if ($productkey_stat['productkey_availability'] == 'Keys Issued') {
                                $message = __('Keys issued for %1.', $_product->getName());
                                $this->messageManager->addSuccess($message);
                            }
                            if ($productkey_stat['stockUpdated'] > 0) {
                                $prdMessage = __($txt, $productkey_stat['stockUpdated'], $keypool);
                                $this->messageManager->addSuccess($prdMessage);
                            }
                        }

                        $this->saveKeysToOrderItems(
                            $item_ids['order_id'],
                            $itemId,
                            implode(',', $issued_productkeys),
                            implode(',', $issued_productkeyIds),
                            $keypool,
                            $keyType,
                            $savedKeysCount
                        );
                    }
                }
            }
        }
        $orderPage = $this->backendUrl->getUrl('sales/order/view', ['order_id' => $item_ids['order_id']]);
        $this->_redirect($orderPage);
    }

    public function fetchProductKeys($orderIncId, $keypool)
    {
        $collection = $this->modelFactory->getCollection()
                    ->addFieldToFilter('orderinc_id', $orderIncId)
                    ->addFieldToFilter('sku', $keypool)
                    ->addFieldToFilter('status', '1');
        return $collection->getData('product_key');
    }

    public function fetchKeysInOrder($orderIncId, $keypool)
    {
        $orderItemCollection = $this->orderItem->getCollection()
                    ->addFieldToFilter('order_id', $orderIncId)
                    ->addFieldToFilter('product_key_pool', $keypool);
        return $orderItemCollection->getData('product_keys_issued');
    }

    public function saveOrderToProductkeys($orderIncId, $keypool, $_product = null, $storeId = null)
    {
        $values = [];
        $collection = $this->modelFactory->getCollection()->addFieldToFilter('sku', $keypool)->count();
        $values['product_key'] = '';
        $values['productkey_id'] = '';
        $values['productkey_availability'] = '';
        $values['stockUpdated'] = 0;
        if ($collection > 0) {
            $available_keys = $this->modelFactory->getCollection()
                        ->addFieldToFilter('sku', $keypool)
                        ->addFieldToFilter('status', '0');

            if (count($available_keys) >= 1) {
                $available_key = $available_keys->getFirstItem();
                $values['product_key'] = $available_key->getProductKey();
                $values['productkey_id'] = $available_key->getId();
                $available_key->setOrderincId($orderIncId);
                $available_key->setStatus(1);
                $available_key->setUpdatedAt($this->dateTime->date()->format('Y-m-d H:i:s'));
                $available_key->save();
                $available_key->unsetData();
                $values['productkey_availability'] = 'Keys Issued';
            } else {
                $values['productkey_availability'] = 'No Keys';
            }

            if ($_product->getProductkeyOverwritegnrlconfig()) {
                $sendLowWarning = $_product->getProductkeyLowWarning();
                $warninglvl_default = $_product->getProductkeyWarningLevel();
                $send_warningTo = $_product->getProductkeyWarningEmail();
                $productkey_type = $_product->getProductkeyType();
                $templateId = $_product->getProductkeyWarningTemplate();
            } else {
                $sendLowWarning = $this->helperData->getGeneralConfig('productkeys_low_warning', $storeId);
                $warninglvl_default = $this->helperData->getGeneralConfig('productkeys_warning_level', $storeId);
                $send_warningTo = $this->helperData->getGeneralConfig('productkeys_warning_email', $storeId);
                $productkey_type = $this->helperData->getGeneralConfig('productkeys_type', $storeId);
                $templateId = $this->helperData->getGeneralConfig('productkeys_warning_template', $storeId);
            }

            //$values['stockUpdated'] = $this->helperData->changeQty($keypool);

            if (!empty($_product) && $sendLowWarning) {
                $min_warning_val = isset($warninglvl_default) ? $warninglvl_default : 5;
                if (empty($productkey_type)) {
                    $productkey_type = 'Product Key';
                }
                if (count($available_keys) <= $min_warning_val+1 && !empty($send_warningTo)) {
                    if (!is_numeric($templateId) || $templateId == 0) {
                        $templates = $this->emailTemplates->create()
                                ->addFieldToFilter('template_code', 'Productkey Warning');
                        foreach ($templates as $template) {
                            $templateId = $template->getTemplateId();
                        }
                    }

                    $available_keyscount = (count($available_keys)-1 >= 0) ? count($available_keys)-1 : 0;
                    $order = $this->orderFactory->loadByIncrementId($orderIncId);
                    $templateVars = [
                        'product_name' => $_product->getName(),
                        'available_keys' => $available_keyscount,
                        'available_keys_none' => !count($available_keys),
                        'keytype' => $productkey_type,
                        'order' => $order,
                        'storeGroupName' => $order->getStoreGroupName(),
                        'keypool' => $keypool
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

                    if (strpos($send_warningTo, ",") !== false) {
                        $values['productkey_availability'] = "There is something wrong in the value for 'Email Low Warning To'. Use ; for email separation.";
                    } else {
                        foreach (explode(";", $send_warningTo) as $email) {
                            try {
                                $transport = $this->transportBuilder->setTemplateIdentifier($templateId)
                                            ->setTemplateOptions($templateOptions)
                                            ->setTemplateVars($templateVars)
                                            ->setFrom($sender)
                                            ->addTo($email)
                                            ->getTransport();
                                $transport->sendMessage();
                            } catch (\Exception $e) {
                                $this->messageManager->addError("Low warning email not sent. ".$e->getMessage());
                            }
                        }
                    }
                }
            }
        } else {
            $values['productkey_availability'] = 'Unable to issue keys for %1. Check configuration.';
        }
        return $values;
    }

    public function saveKeysToOrderItems(
        $orderEntityId,
        $itemId,
        $productKeys,
        $productkeyIds,
        $keypool,
        $keytype,
        $issuedKeysCount
    ) {
        $orderItem = $this->orderItem->getCollection()
                    ->addFieldToFilter('item_id', $itemId)
                    ->addFieldToFilter('order_id', $orderEntityId);

        if (count($orderItem) >= 1) {
            $orderItem = $orderItem->getFirstItem();
            $orderItem->setProductKeyType($keytype);
            $orderItem->setProductKeys($productKeys);
            $orderItem->setProductKeyIds($productkeyIds);
            $orderItem->setProductKeysIssued($issuedKeysCount);
            $orderItem->setProductKeyPool($keypool);
            $orderItem->save();
            $orderItem->unsetData();
        }
    }
}
