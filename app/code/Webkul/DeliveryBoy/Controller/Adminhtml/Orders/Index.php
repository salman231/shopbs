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
namespace Webkul\DeliveryBoy\Controller\Adminhtml\Orders;

class Index extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\Registry $coreRegistry
     */
    protected $coreRegistry;

    /**
     * @var \Magento\Ui\Component\MassAction\Filter
     */
    protected $filter;

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
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Ui\Component\MassAction\Filter $filter
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Webkul\DeliveryBoy\Api\RatingRepositoryInterface $ratingRepository
     * @param \Webkul\DeliveryBoy\Api\Data\RatingInterfaceFactory $ratingDataFactory
     * @param \Webkul\DeliveryBoy\Model\ResourceModel\Rating\CollectionFactory $collectionFactory
     */
    public function __construct(
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Backend\App\Action\Context $context,
        \Magento\Ui\Component\MassAction\Filter $filter,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Webkul\DeliveryBoy\Api\RatingRepositoryInterface $ratingRepository,
        \Webkul\DeliveryBoy\Api\Data\RatingInterfaceFactory $ratingDataFactory,
        \Webkul\DeliveryBoy\Model\ResourceModel\Rating\CollectionFactory $collectionFactory
    ) {
        parent::__construct($context);
        $this->filter = $filter;
        $this->coreRegistry = $coreRegistry;
        $this->ratingRepository = $ratingRepository;
        $this->collectionFactory = $collectionFactory;
        $this->resultPageFactory = $resultPageFactory;
        $this->ratingDataFactory = $ratingDataFactory;
    }

    /**
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend(__("Manage Delivery Boy Orders"));
        return $resultPage;
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed("Webkul_DeliveryBoy::deliveryboyorders");
    }
}
