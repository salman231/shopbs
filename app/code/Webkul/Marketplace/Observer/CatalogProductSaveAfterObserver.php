<?php

/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Marketplace
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Marketplace\Observer;

use Magento\Framework\Event\ObserverInterface;
use Webkul\Marketplace\Model\ResourceModel\Product\CollectionFactory;
use Webkul\Marketplace\Model\ProductFactory as MpProductFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Webkul\Marketplace\Model\ResourceModel\Product\CollectionFactory as MpProductCollection;
use Magento\ConfigurableProduct\Api\LinkManagementInterface;
use Webkul\Marketplace\Helper\Data as MpHelper;
use Magento\Framework\Api\SearchCriteriaBuilder;

/**
 * Webkul Marketplace CatalogProductSaveAfterObserver Observer.
 */
class CatalogProductSaveAfterObserver implements ObserverInterface {

    /**
     * @var CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_date;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @var MpProductFactory
     */
    protected $mpProductFactory;

    /**
     * @var MpHelper
     */
    protected $mpHelper;

    /**
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param CollectionFactory                           $collectionFactory
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param MpProductFactory                            $mpProductFactory
     * @param MpHelper                                    $mpHelper
     */
    public function __construct(
    \Magento\Framework\Stdlib\DateTime\DateTime $date, CollectionFactory $collectionFactory, \Magento\Framework\Message\ManagerInterface $messageManager, MpProductFactory $mpProductFactory, LinkManagementInterface $linkManagement, MpProductCollection $mpProductCollectionFactory, ProductRepositoryInterface $productRepository, SearchCriteriaBuilder $searchCriteriaBuilder, MpHelper $mpHelper
    ) {
        $this->_collectionFactory = $collectionFactory;
        $this->_date = $date;
        $this->linkManagement = $linkManagement;
        $this->productRepository = $productRepository;
        $this->messageManager = $messageManager;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->_mpProductCollectionFactory = $mpProductCollectionFactory;
        $this->mpProductFactory = $mpProductFactory;
        $this->mpHelper = $mpHelper;
    }

    /**
     * Product save after event handler.
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer) {
        try {
            $product = $observer->getProduct();
            $assginSellerData = $product->getAssignSeller();

            $productId = $observer->getProduct()->getId();
            $status = $observer->getProduct()->getStatus();
            $productCollection = $this->mpProductFactory->create()
                    ->getCollection()
                    ->addFieldToFilter(
                    'mageproduct_id', $productId
            );

            if (is_array($assginSellerData) &&
                    isset($assginSellerData['seller_id']) &&
                    $assginSellerData['seller_id'] != '' && $product->getTypeId() == \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE
            )
                {
                $sellerId = $assginSellerData['seller_id'];

                $searchCriteria = $this->searchCriteriaBuilder
                        ->addFilter('type_id', 'configurable')
                        ->addFilter('entity_id', $productId)
                        ->create();

                $configurableProducts = $this->productRepository
                        ->getList($searchCriteria);
                $associatedProductIds = array();
                $childProducts = [];
                foreach ($configurableProducts->getItems() as $configurableProduct) {
                    $childProducts = $this->linkManagement->getChildren($configurableProduct->getSku());
                }
                foreach ($childProducts as $child) {
                    array_push($associatedProductIds, $child->getId());
                }
                foreach ($associatedProductIds as $associatedProductId) {
                    if ($associatedProductId) {
                        $sellerAssociatedProductId = 0;
                        $sellerProductColls = $this->_mpProductCollectionFactory->create()
                                ->addFieldToFilter(
                                        'mageproduct_id', $associatedProductId
                                )
                                ->addFieldToFilter(
                                'seller_id', $sellerId
                        );
                        foreach ($sellerProductColls as $sellerProductColl) {
                            $sellerAssociatedProductId = $sellerProductColl->getId();
                        }
                        $collection1 = $this->mpProductFactory->create()->load($sellerAssociatedProductId);
                        $collection1->setMageproductId($associatedProductId);
                        $collection1->setUpdatedAt($this->_date->gmtDate());
                        $collection1->setSellerId($sellerId);
                        $collection1->setIsApproved(1);
                        $collection1->save();
                    }
                }

            }
            if ($productCollection->getSize()) {
                foreach ($productCollection as $product) {
                    if ($status != $product->getStatus()) {
                        $product->setStatus($status)->save();
                    }
                }
            } elseif (is_array($assginSellerData) &&
                    isset($assginSellerData['seller_id']) &&
                    $assginSellerData['seller_id'] != ''
            ) {
                $sellerId = $assginSellerData['seller_id'];
                $mpProductModel = $this->mpProductFactory->create();
                $mpProductModel->setMageproductId($productId);

                $mpProductModel->setSellerId($sellerId);
                $mpProductModel->setStatus($product->getStatus());
                $mpProductModel->setAdminassign(1);
                $isApproved = 1;
                if ($product->getStatus() == 2 && $this->mpHelper->getIsProductApproval()) {
                    $isApproved = 0;
                }
                $mpProductModel->setIsApproved($isApproved);
                $mpProductModel->setCreatedAt($this->_date->gmtDate());
                $mpProductModel->setUpdatedAt($this->_date->gmtDate());
                $mpProductModel->save();
            }
        } catch (\Exception $e) {
            $this->mpHelper->logDataInLogger(
                    "Observer_CatalogProductSaveAfterObserver execute : " . $e->getMessage()
            );
            $this->messageManager->addError($e->getMessage());
        }
    }

}
