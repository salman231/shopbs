<?php
/**
 * Webkul Software.
 *
 * PHP version 7.0+
 *
 * @category  Webkul
 * @package   Webkul_DeliveryBoy
 * @author    Webkul <support@webkul.com>
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html ASL Licence
 * @link      https://store.webkul.com/license.html
 */
namespace Webkul\DeliveryBoy\Controller\Adminhtml;

use Webkul\DeliveryBoy\Model\ResourceModel\Deliveryboy\CollectionFactory as DeliveryboyResourceCollectionF;
use Magento\Customer\Model\CustomerFactory;
use Webkul\DeliveryBoy\Helper\Data as DeliveryboyDataHelper;
use Psr\Log\LoggerInterface;

abstract class Rating extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Ui\Component\MassAction\Filter
     */
    protected $filter;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * @var \Webkul\DeliveryBoy\Api\RatingRepositoryInterface
     */
    protected $ratingRepository;

    /**
     * @var \Webkul\DeliveryBoy\Model\ResourceModel\Rating\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Webkul\DeliveryBoy\Api\Data\RatingInterfaceFactory
     */
    protected $ratingDataFactory;

    /**
     * @var DeliveryboyResourceCollectionF
     */
    protected $deliveryboyResourceCollectionF;

    /**
     * @var CustomerFactory
     */
    protected $customerF;

    /**
     * @var DeliveryboyDataHelper
     */
    protected $deliveryboyDataHelper;

    /**
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Ui\Component\MassAction\Filter $filter
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Webkul\DeliveryBoy\Api\RatingRepositoryInterface $ratingRepository
     * @param \Webkul\DeliveryBoy\Api\Data\RatingInterfaceFactory $ratingDataFactory
     * @param \Webkul\DeliveryBoy\Model\ResourceModel\Rating\CollectionFactory $collectionFactory
     * @param DeliveryboyResourceCollectionF $deliveryboyResourceCollectionF
     * @param CustomerFactory $customerF
     * @param DeliveryboyDataHelper $deliveryboyDataHelper
     */
    public function __construct(
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Backend\App\Action\Context $context,
        \Magento\Ui\Component\MassAction\Filter $filter,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Webkul\DeliveryBoy\Api\RatingRepositoryInterface $ratingRepository,
        \Webkul\DeliveryBoy\Api\Data\RatingInterfaceFactory $ratingDataFactory,
        \Webkul\DeliveryBoy\Model\ResourceModel\Rating\CollectionFactory $collectionFactory,
        DeliveryboyResourceCollectionF $deliveryboyResourceCollectionF,
        CustomerFactory $customerF,
        DeliveryboyDataHelper $deliveryboyDataHelper,
        LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->filter = $filter;
        $this->coreRegistry = $coreRegistry;
        $this->ratingRepository = $ratingRepository;
        $this->collectionFactory = $collectionFactory;
        $this->resultPageFactory = $resultPageFactory;
        $this->ratingDataFactory = $ratingDataFactory;
        $this->deliveryboyResourceCollectionF = $deliveryboyResourceCollectionF;
        $this->customerF = $customerF;
        $this->deliveryboyDataHelper = $deliveryboyDataHelper;
        $this->logger = $logger;
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed("Webkul_DeliveryBoy::rating");
    }
}
