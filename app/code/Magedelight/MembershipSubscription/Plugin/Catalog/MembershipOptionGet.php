<?php

namespace Magedelight\MembershipSubscription\Plugin\Catalog;

use Magento\Catalog\Model\Product as ProductModel;
use Magento\Catalog\Api\Data\ProductExtensionFactory;
use Magedelight\MembershipSubscription\Model\ResourceModel\MembershipProducts\CollectionFactory as AdditionCollectionFactory;

class MembershipOptionGet
{

    public $membershipProductRepository;

    public $membershipProductOption;

    /**
     * @var \Magedelight\MembershipSubscription\Helper\Data
     */
    protected $membershipHelper;

    public function __construct(
        \Magedelight\MembershipSubscription\Model\MembershipProductRepository $membershipProductRepository,
        \Magedelight\MembershipSubscription\Api\Data\MembershipProductOptionInterface $membershipProductOption,
        \Magedelight\MembershipSubscription\Helper\Data $membershipHelper
    ) {
        $this->membershipProductRepository = $membershipProductRepository;
        $this->membershipProductOption = $membershipProductOption;
        $this->membershipHelper = $membershipHelper;
    }

    public function afterGet(
        \Magento\Catalog\Api\ProductRepositoryInterface $subject,
        \Magento\Catalog\Api\Data\ProductInterface $resultOrder
    ) {
        $product = $this->getMembershipOptions($resultOrder);
        return $product;
    }


    public function getMembershipOptions(\Magento\Catalog\Api\Data\ProductInterface $product)
    {
        if ($product->getTypeId() == 'Membership') {
            $membershipProduct = $this->membershipProductRepository->getById($product->getId());
            $membershipDuration = $this->membershipHelper->unserialize($membershipProduct->getMembershipDuration());
            $this->membershipProductOption->setFeatured($membershipProduct->getFeatured());
            $this->membershipProductOption->setMembershipDuration($membershipDuration);
            $extensionAttribute = $product->getExtensionAttributes();
            $extensionAttribute->setMembershipProductOptions($this->membershipProductOption);
        }
        return $product;
    }
}
