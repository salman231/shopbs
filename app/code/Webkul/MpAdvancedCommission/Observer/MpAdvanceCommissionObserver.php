<?php
/**
 * @category   Webkul
 * @package    Webkul_MpAdvancedCommission
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\MpAdvancedCommission\Observer;

use Magento\Framework\Event\ObserverInterface;

/**
 * Webkul MpAdvancedCommission MpAdvanceCommissionObserver Observer Model.
 */
class MpAdvanceCommissionObserver implements ObserverInterface
{
    /**
     * @var eventManager
     */
    protected $_eventManager;

    /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @var \Webkul\Marketplace\Helper\Data
     */
    protected $_mpHelper;

    /**
     * @var \Magento\Catalog\Model\CategoryFactory
     */
    protected $_catalogCategory;

    /**
     * @var \Magento\Customer\Model\Customer
     */
    protected $_customerFactory;

    /**
     * @var \Webkul\MpAdvancedCommission\Model\CommissionRulesFactory
     */
    protected $_commissionruleFactory;

    /**
     * @var \Webkul\MpAdvancedCommission\Helper\Data
     */
    protected $_commissionHelper;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $_productFactory;

     /**
      * @param \Magento\Framework\Event\Manager $eventManager
      * @param \Magento\Framework\ObjectManagerInterface $objectManager
      * @param \Magento\Customer\Model\Session $customerSession
      * @param \Magento\Checkout\Model\Session $checkoutSession
      * @param \Webkul\Marketplace\Helper\Data $mpHelper
      * @param \Magento\Catalog\Model\CategoryFactory $catalogCategory
      * @param \Magento\Customer\Model\Customer $customerFactory
      * @param \Webkul\MpAdvancedCommission\Model\CommissionRulesFactory $commissionruleFactory
      * @param \Magento\Quote\Model\QuoteFactory $quote
      * @param \Webkul\MpAdvancedCommission\Helper\Data $commissionHelper
      * @param \Magento\Catalog\Model\ProductFactory $productFactory
      */
    public function __construct(
        \Magento\Framework\Event\Manager $eventManager,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Webkul\Marketplace\Helper\Data $mpHelper,
        \Magento\Catalog\Model\CategoryFactory $catalogCategory,
        \Magento\Customer\Model\Customer $customerFactory,
        \Webkul\MpAdvancedCommission\Model\CommissionRulesFactory $commissionruleFactory,
        \Magento\Quote\Model\QuoteFactory $quote,
        \Webkul\MpAdvancedCommission\Helper\Data $commissionHelper,
        \Magento\Catalog\Model\ProductFactory $productFactory
    ) {
        $this->_eventManager = $eventManager;
        $this->_objectManager = $objectManager;
        $this->_customerSession = $customerSession;
        $this->_checkoutSession = $checkoutSession;
        $this->_mpHelper = $mpHelper;
        $this->_catalogCategory = $catalogCategory;
        $this->_customerFactory = $customerFactory;
        $this->_commissionruleFactory = $commissionruleFactory;
        $this->_quote=$quote;
        $this->_commissionHelper = $commissionHelper;
        $this->_productFactory = $productFactory;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try {
             /** @var $orderInstance Order */
            $productId = $observer->getId();
            if (!$productId) {
                $eventData = $observer->getData();
                $productId = $eventData[0]['id'];
            }
            $product = $this->_productFactory->create(
            )->load($productId);
            $categoryArray = [];
        
            $quoteId = $this->_checkoutSession->getLastQuoteId();

            $helper =  $this->_commissionHelper;
            $proCommission = 0;
            if (!empty($quoteId)) {
                 $quote = $this->_quote->create(
                 )->load($quoteId);
                  $sellerData = $helper->getSellerData($quote);
                if ($helper->getUseCommissionRule()) {
                    $categoryArray=$this->getProCommissionOnCommissionRule($sellerData);
                }
                $categoryCount=count($categoryArray);
                if ($categoryCount!==0) {
                    foreach ($categoryArray as $key => $value) {
                        if ($key==$productId) {
                            $proCommission = $value['amount'];
                        }
                    }
                } else {
                    $proCommission = $product->getCommissionForProduct();
                    $proCommission= $this->getProCommissionWhenNull($proCommission, $product);
                }
                    $productPrice = $product->getFinalPrice();
                if ($proCommission > $productPrice) {
                        $mpGlobalCommission = $this->_mpHelper->getConfigCommissionRate();
                        $commType =  $this->_mpHelper->getCommissionType();
                    if ($commType == 'fixed') {
                        $proCommission = ($productPrice*$mpGlobalCommission)/100;
                    } else {
                        $proCommission = $mpGlobalCommission;
                    }
                }
            }
            $this->_customerSession->setData('commission', $proCommission);
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    private function getProCommissionWhenNull($proCommission, $product)
    {
        $productPrice = $product->getFinalPrice();
        $productId = $product->getId();
        if ($proCommission == 0 || $proCommission == '' || $proCommission==null || ($proCommission > $productPrice)) {
            $categoryArray = [];
            $sellerProduct = $this->_mpHelper->getSellerProductDataByProductId($productId);
            $sellerId = '';
            foreach ($sellerProduct as $key => $value) {
                $sellerId = $value['seller_id'];
            }

            $seller =  $this->_customerFactory->load($sellerId);

            $categoryCommission = [];
            if ($seller->getCategoryCommission()) {
                $categoryCommission = array_filter(
                    json_decode($seller->getCategoryCommission(), true)
                );
            }
            $category = $this->_catalogCategory->create();
            foreach ($product->getCategoryIds() as $id) {
                if (isset($categoryCommission[$id])) {
                    array_push($categoryArray, $categoryCommission[$id]);
                } else {
                    foreach ($product->getCategoryIds() as $id) {
                        $adminCommission =$this->getAdminCommissionFromCategory($id);
                        array_push($categoryArray, $adminCommission);
                    }
                }
            }
            $categoryArrayCount=count($categoryArray);
            if ($categoryArrayCount!==0 && $categoryArray[0]!=="" && $categoryArray[0]!==null) {
                $proCommission = max($categoryArray);
            }
        }
        return $proCommission;
    }
    private function getProCommissionOnCommissionRule($sellerData)
    {
        $categoryArray=[];
        foreach ($sellerData as $sellerId => $row) {
            $commissionRuleCollection = $this->_commissionruleFactory->create(
            )
            ->getCollection()
            ->addFieldToFilter(
                "price_from",
                ["lteq" => round($row['total'])]
            )
            ->addFieldToFilter(
                "price_to",
                ["gteq" => round($row['total'])]
            );
            if (empty($commissionRuleCollection)) {
                $commissionRuleCollection = $this->_commissionruleFactory->create(
                )
                ->getCollection()
                ->addFieldToFilter(
                    "price_from",
                    ["lteq" => round($row['total'])]
                )
                ->addFieldToFilter(
                    "price_to",
                    "*"
                );
            }
            foreach ($commissionRuleCollection as $commissionRule) {
                if ($commissionRule->getCommissionType() == "percent") {
                    foreach ($row['details'] as $item) {
                        $categoryArray[$item['product_id']] = [
                            "amount" => $item['price']*$commissionRule->getAmount()/100,
                            "type" => $commissionRule->getCommissionType()
                        ];
                    }
                } else {
                    foreach ($row['details'] as $item) {
                        $totalSellerAmount = $sellerData[$sellerId]['total'];
                        $perComPro = $commissionRule->getAmount()*100/$totalSellerAmount;
                        $categoryArray[$item['product_id']] = [
                            "amount" => $item['price']*$perComPro/100,
                            "type" => $commissionRule->getCommissionType()
                        ];
                    }
                }
                break;
            }
        }
        return $categoryArray;
    }
    /**
     * return commission amount from category
     *
     * @param int $id
     * @return int
     */
    private function getAdminCommissionFromCategory($id)
    {
        $adminCommission='';
        if (!empty($id)) {
            $category = $this->_catalogCategory->create()->
            load($id);
            $adminCommission =$category->getCommissionForAdmin();
            return $adminCommission;
        }
        return $adminCommission;
    }
}
