<?php

namespace Magedelight\MembershipSubscription\Plugin\Catalog;

use Magento\Catalog\Model\Product as ProductModel;
use Magento\Catalog\Api\Data\ProductExtensionFactory;
use Magedelight\MembershipSubscription\Model\ResourceModel\MembershipProducts\CollectionFactory as AdditionCollectionFactory;

class MembershipOptionSave
{

    public $membershipProductRepository;

    public function __construct(\Magedelight\MembershipSubscription\Model\MembershipProductRepository $membershipProductRepository)
    {
        $this->membershipProductRepository = $membershipProductRepository;
    }

    public function afterSave(
        \Magento\Catalog\Api\ProductRepositoryInterface $subject,
        \Magento\Catalog\Api\Data\ProductInterface $resultOrder
    ) {
        $resultOrder = $this->saveMembershipOptions($resultOrder);

        return $resultOrder;
    }


    public function saveMembershipOptions(\Magento\Catalog\Api\Data\ProductInterface $product)
    {

        $extensionAttributes = $product->getExtensionAttributes();
    }
}
