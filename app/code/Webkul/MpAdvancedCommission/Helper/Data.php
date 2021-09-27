<?php
/**
 * @category   Webkul
 * @package    Webkul_MpAdvancedCommission
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\MpAdvancedCommission\Helper;

/**
 * data helper.
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\App\Helper\Context $context
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Quote\Model\Quote\Item\OptionFactory $optionFactory,
        \Webkul\Marketplace\Model\ProductFactory $productFactory
    ) {
        $this->_objectManager = $objectManager;
        $this->_optionFactory= $optionFactory;
        $this->_productFactory = $productFactory;
        parent::__construct($context);
    }

    /**
     * check whether to use commission rule or not
     *
     * @return void
     */
    public function getUseCommissionRule()
    {
        return $this->scopeConfig->getValue(
            'mpadvancedcommission/options/use_commission_rule',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * get Commission type applied
     *
     * @return void
     */
    public function getCommissionType()
    {
        return $this->scopeConfig->getValue(
            'mpadvancedcommission/options/commission_type',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * get seller data associated with the order
     *
     * @param collection $order
     * @return array
     */
    public function getSellerData($order)
    {
        $sellerData = [];
        $qty = 1;
        foreach ($order->getAllVisibleItems() as $item) {
            $sellerId = $this->getSellerIdByItem($item);
            if (!isset($sellerData[$sellerId]['details'])) {
                $sellerData[$sellerId]['details'] = [];
                $sellerData[$sellerId]['total'] = 0;
            }
            array_push(
                $sellerData[$sellerId]['details'],
                [
                    'product_id'=>$item->getProductId(),
                    'item_id'=>$item->getId(),
                    'price'=>$item->getPrice() * $qty
                ]
            );
            $sellerData[$sellerId]['total']+=$item->getPrice() * $qty;
        }
        return $sellerData;
    }

    /**
     * get seller Id by item
     *
     * @param array $item
     * @return int
     */
    public function getSellerIdByItem($item = [])
    {
        $mpassignproductId = 0;
        $sellerId = "";
        $options = $item->getProductOptions();
        if ($item->getQtyOrdered()) {
            $qty = $item->getQtyOrdered();
        } else {
            $qty = $item->getQty();
        }
        $itemOption = $this->_optionFactory->create()
        ->getCollection()
        ->addFieldToFilter('item_id', $item->getId())
        ->addFieldToFilter('code', 'info_buyRequest');
        
        if (!empty($itemOption)) {
            foreach ($itemOption as $option) {
                $temp = json_decode($option['value'], true);
                if (isset($temp['mpassignproduct_id'])) {
                    $mpassignproductId = $temp['mpassignproduct_id'];
                }
            }
        }

        if ($mpassignproductId) {
            $mpassignModel = $this->_objectManager->create(
                \Webkul\MpAssignProduct\Model\Items::class
            )->load($mpassignproductId);
            $sellerId = $mpassignModel->getSellerId();
        } else {
            $collectionProduct = $this->_productFactory->create()
            ->getCollection()
            ->addFieldToFilter(
                'mageproduct_id',
                $item->getProductId()
            );
            foreach ($collectionProduct as $value) {
                $sellerId = $value->getSellerId();
            }
        }
        if ($sellerId == "") {
            $sellerId = 0;
        }
        return $sellerId;
    }
}
