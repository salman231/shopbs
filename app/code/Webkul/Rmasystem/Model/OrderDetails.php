<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Rmasystem
 * @author    Webkul
 * @copyright Copyright (c) 2010-2018 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Rmasystem\Model;

use Magento\Sales\Model\OrderRepository;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Catalog\Model\ProductRepository;
use Webkul\Rmasystem\Model\ResourceModel\Rmaitem\CollectionFactory;
use Webkul\Rmasystem\Model\AllrmaFactory;
use Magento\Framework\Exception\NoSuchEntityException;

class OrderDetails implements \Webkul\Rmasystem\Api\OrderDetailsInterface
{
  /**
   * @var OrderRepository
   */
    protected $orderRepository;

    /**
     * @var ProductRepository
     */
    protected $productRepository;

    /**
     * @var CollectionFactory
     */
    protected $itemCollectionFactory;

    /**
     * @var Webkul\Rmasystem\Model\AllrmaFactory
     */
    protected $rmaFactory;

    /** @var DataObjectHelper  */
    protected $dataObjectHelper;

    /**
     * @var \Magento\Store\Model\ScopeInterface
     */
    protected $_scopeConfig;

    /**
     * @var CollectionFactory
     */
    private $customerSession;

    /**
     * @var \Webkul\Rmasystem\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Catalog\Helper\Image
     */
    protected $_imageHelper;

    protected $appEmulation;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;

    /**
     * @param OrderRepository $orderRepository
     * @param ProductRepository $productRepository
     * @param CollectionFactory $itemCollectionFactory
     * @param AllrmaFactory $rmaFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeInterface
     * @param \Magento\Catalog\Helper\Image $imageHelper
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Webkul\Rmasystem\Helper\Data $helper
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Store\Model\App\Emulation $appEmulation
     */
    public function __construct(
        OrderRepository $orderRepository,
        ProductRepository $productRepository,
        CollectionFactory $itemCollectionFactory,
        AllrmaFactory $rmaFactory,
        DataObjectHelper $dataObjectHelper,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeInterface,
        \Magento\Catalog\Helper\Image $imageHelper,
        \Magento\Customer\Model\Session $customerSession,
        \Webkul\Rmasystem\Helper\Data $helper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Store\Model\App\Emulation $appEmulation,
        \Magento\Framework\Json\Helper\Data $jsonHelper
    ) {
        $this->orderRepository = $orderRepository;
        $this->productRepository = $productRepository;
        $this->itemCollectionFactory = $itemCollectionFactory;
        $this->rmaFactory = $rmaFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->scopeConfig = $scopeInterface;
        $this->_imageHelper = $imageHelper;
        $this->customerSession = $customerSession;
        $this->helper = $helper;
        $this->storeManager = $storeManager;
        $this->appEmulation = $appEmulation;
        $this->jsonHelper = $jsonHelper;
    }

    /**
     * Returns selected order detail
     *
     * @api
     * @param int $orderId
     * @return string.
     */
    public function getDetails($orderId)
    {
        $allowedProductsType = explode(',', $this->helper->getConfigData('allow_for_rma'));
        $allowedPaymentMethods = explode(',', $this->helper->getConfigData('payment_allow_for_rma'));
        $orderDetails['orderDetails'] = [];
        $delivery_items = true;
        try {
            $order = $this->orderRepository->get($orderId);
        } catch (NoSuchEntityException $e) {
            array_push(
                $orderDetails,
                [
                  'error' => true,
                  'message' => $e->getMessage()
                ]
            );
            return json_encode($orderDetails);
        }
        $allItems = $order->getAllVisibleItems();
        $totalRmaReturned = 0;
        $orderPaymentMethod = $order->getPayment()->getMethod();
        //payment method check
        if (!in_array($orderPaymentMethod, $allowedPaymentMethods)) {
            return $this->jsonHelper->jsonEncode($orderDetails);
        }
        foreach ($allItems as $item) {
            $disable = false;
            $qtyOrder = $item->getQtyOrdered();
            $qtyInvoiced = $item->getQtyInvoiced();
            if (!$item->getProduct()) {
                continue;
            }
            if ($item->getProductType() == 'virtual' || $item->getProductType() == 'downloadable') {
                $delivery_items = false;
            }
            if (!in_array($item->getProductType(), $allowedProductsType) || $item->getParentItem()) {
                continue;
            }

            $returnedQty = 0;
            $activeRmaFound = false;
            $rmaStatus = null;
            $activeRmaQty = 0;

            $itemCollection = $this->itemCollectionFactory->create()
                ->addFieldToFilter('order_id', ['eq' => $orderId])
                ->addFieldToFilter('item_id', ['eq' => $item->getItemId()]);

            if (count($itemCollection)) {
                if ($this->helper->getConfigData('active_after_decline')
                    || $this->helper->getConfigData('active_after_cancel')) {
                    foreach ($itemCollection as $rmaItem) {
                        $rma = $this->rmaFactory->create()->load($rmaItem->getRmaId());
                        if ($rma->getStatus() != 3 && $rma->getStatus() != 4) {
                            $activeRmaFound = true;
                            $activeRmaQty = $rmaItem->getQty();
                            break;
                        }
                        $rmaStatus = $rma->getStatus();
                    }
                }
                
                $itemCollection->addFieldToSelect('qty');
                $returnedQuanties = $itemCollection->getColumnValues('qty');
                foreach ($returnedQuanties as $value) {
                    $returnedQty+=$value;
                }
            }

            /**
             * check item should allow to RMA
             */
            $this->_checkDisableRmaItem(
                $item,
                $rmaStatus,
                $activeRmaFound,
                $returnedQty,
                $activeRmaQty,
                $disable,
                $totalRmaReturned
            );
            
            $product = $this->productRepository->getById(
                $item->getProductId()
            );
            //category filter
            $catIds = $product->getCategoryIds();
            if (!$this->helper->isCategoryAllowed($catIds)) {
                continue;
            }

            $url = $product->getProductUrl();

            $storeId = $this->storeManager->getStore()->getId();
            // emulate the frontend environment
            $this->appEmulation->startEnvironmentEmulation($storeId, \Magento\Framework\App\Area::AREA_FRONTEND, true);
            $imageUrl = $this->_imageHelper->init($product, 'product_page_image_small')
                            ->setImageFile($product->getImage())
                            ->keepAspectRatio(true)
                            ->resize(100, 100)
                            ->getUrl();
            $this->appEmulation->stopEnvironmentEmulation();

            array_push(
                $orderDetails['orderDetails'],
                [
                    'url' => $url,
                    'image' => $imageUrl,
                    'name' => $item->getName(),
                    'sku' => $item->getSku(),
                    'qty' => (int) $item->getQtyOrdered(),
                    'itemid' => $item->getItemId(),
                    'product_id' => $item->getProductId(),
                    'price' => $order->formatPrice($item->getPrice() - $item->getDiscountAmount()),
                    'returnedQty' => $returnedQty,
                    'disabled' => $disable,
                    'error' => false
                ]
            );
        }

        if ($order->getStatus() == 'complete') {
            $type = 2;
            $orderDetails['orderStatus'] = "processing";
        } elseif ($order->getStatus() == 'processing') {
            $type = 1;
            $orderDetails['orderStatus'] = "processing";
        } elseif ($order->getStatus() == 'pending') {
            $type = 0;
            $orderDetails['orderStatus'] = "pending";
        } else {
            $type = 3;
            $orderDetails['orderStatus'] = $order->getStatus();
        }
        //invoice status
        $invoice_status = 1;
        if ($order->hasInvoices()) {
            $invoice_status = 2;
        }

        $orderDetails['deliverable'] = $delivery_items;
        $orderDetails['resolutionsTypes'] = $this->helper->getResolutionTypes($invoice_status);
        $orderDetails['deliverStatus'] = $this->helper->getDeliveryStatus($type);
        if ($totalRmaReturned === count($orderDetails['orderDetails'])) {
            $type = 0;
        }
        $orderDetails['rmaType'] = $type;
        return $this->jsonHelper->jsonEncode($orderDetails);
    }

    /**
     * Check RMA is allowed for item
     *
     * @param object $item
     * @param int $rmaStatus
     * @param boolean $activeRmaFound
     * @param int $returnedQty
     * @param int $activeRmaQty
     * @param boolean $disable
     * @param int $totalRmaReturned
     * @return void
     */
    protected function _checkDisableRmaItem(
        $item,
        $rmaStatus,
        $activeRmaFound,
        $returnedQty,
        $activeRmaQty,
        &$disable,
        &$totalRmaReturned
    ) {
        if ($returnedQty == $item->getQtyOrdered()) {
            $disable = true;
            $totalRmaReturned++;
        }
        if ($activeRmaFound == true && $activeRmaQty == $item->getQtyOrdered()) {
            $disable = true;
        } elseif ($activeRmaFound == false && $rmaStatus == 3 && $this->helper->getConfigData('active_after_decline')) {
            $disable = false;
            $totalRmaReturned--;
        } elseif ($activeRmaFound == false && $rmaStatus == 4 && $this->helper->getConfigData('active_after_cancel')) {
            $disable = false;
            $totalRmaReturned--;
        }
    }

    /**
     *
     */
    public function getAllowedStatus()
    {
        return $this->scopeConfig->getValue(
            'rmasystem/parameter/allow_order',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
}
