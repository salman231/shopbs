<?php
namespace Magedelight\MembershipSubscription\Model;

use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\ValidatorException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magedelight\MembershipSubscription\Api\MembershipProductRepositoryInterface;
use Magedelight\MembershipSubscription\Api\Data\MembershipProductInterfaceFactory;
use Magedelight\MembershipSubscription\Model\ResourceModel\MembershipProducts as ResourceMembershipProducts;
use Magento\Framework\Api\ExtensibleDataObjectConverter;

/**
 * Class AuthorRepository
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class MembershipProductRepository implements MembershipProductRepositoryInterface
{
     /**
      * @var array
      */
    protected $instances = [];

    public $extensibleDataObjectConverter;


    public function __construct(
        ResourceMembershipProducts $resource,
        MembershipProductInterfaceFactory $membershipProductInterfaceFactory,
        ExtensibleDataObjectConverter $extensibleDataObjectConverter,
        MembershipProductsFactory $membershipProductsFactory
    ) {
        $this->resource = $resource;
        $this->membershipProductInterfaceFactory = $membershipProductInterfaceFactory;
        $this->extensibleDataObjectConverter = $extensibleDataObjectConverter;
    }

    /**
     * @inheritDoc

     */
    public function getById($membershipProductId)
    {
        if (!isset($this->instances[$membershipProductId])) {
            /** @var \Magedelight\MembershipSubscription\Api\Data\MembershipProductInterface|\Magento\Framework\Model\AbstractModel $author */
            $membershipProduct = $this->membershipProductInterfaceFactory->create();
            $this->resource->load($membershipProduct, $membershipProductId, 'product_id');
            if (!$membershipProduct->getId()) {
                throw new NoSuchEntityException(__('Requested Membership Product doesn\'t exist'));
            }
            $this->instances[$membershipProductId] = $membershipProduct;
        }
        return $this->instances[$membershipProductId];
    }

    /**
     * @inheritDoc
     */
    public function save(\Magedelight\MembershipSubscription\Api\Data\MembershipProductInterface $membershipProduct)
    {
        try {
            $this->resource->save($membershipProduct);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the membership product: %1',
                $exception->getMessage()
            ));
        }
        return $membershipProduct;
    }
}
