<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Rmasystem
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Rmasystem\Block\Adminhtml\Allrma;

use Webkul\Rmasystem\Model\ResourceModel\Rmaitem\CollectionFactory as ItemCollectionFactory;
use Magento\Sales\Model\OrderRepository;
use Webkul\Rmasystem\Api\AllRmaRepositoryInterface;
use Webkul\Rmasystem\Api\Data\AllrmaInterfaceFactory;

class Edit extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * @var \Magento\Directory\Model\Currency
     */
    protected $_currency;
    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_date;
    /**
     * @var ItemCollectionFactory
     */
    protected $itemCollectionFactory;

    /**
     * @var \Webkul\Rmasystem\Api\Data\RmaitemInterfaceFactory
     */
    protected $rmaItemDataFactory;

    /**
     * @var \Webkul\Rmasystem\Api\RmaitemRepositoryInterface
     */
    protected $rmaItemRepository;

    /**
     * @var \Webkul\Rmasystem\Api\Data\ShippinglabelInterface
     */
    protected $labelCollectionDataFactory;

    /**
     * @var \Webkul\Rmasystem\Api\Data\ConversationInterfaceFactory
     */
    protected $conversationDataFactory;

    /**
     * @var \Webkul\Rmasystem\Api\ConversationRepositoryInterface
     */
    protected $conversationRepository;

    /**
     * @var OrderRepository
     */
    protected $orderRepository;

    /**
     * @var AllRmaRepositoryInterface
     */
    protected $rmaRepository;

    /**
     * @var AllrmaInterfaceFactory
     */
    protected $rmaFactory;

    /**
     * @var \Webkul\Rmasystem\Api\Data\ReasonRepositoryInterface
     */
    protected $reasonRepository;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var \Magento\Sales\Model\Order\ItemRepository
     */
    protected $orderItemRepository;

    /**
     * @var \Magento\Customer\Model\ResourceModel\CustomerRepository
     */
    protected $customerRepository;

    /**
     * @var \Webkul\Rmasystem\Helper\Data
     */
    protected $helper;

    /**
     * Core registry.
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Directory\Model\Currency $currency
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Magento\Framework\View\Element\FormKey $formKey
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Webkul\Rmasystem\Model\ResourceModel\Allrma\CollectionFactory $rmaCollectionFactory
     * @param \Webkul\Rmasystem\Model\ResourceModel\Reason\CollectionFactory $regionCollectionFactory
     * @param \Webkul\Rmasystem\Model\ResourceModel\Rmaitem\CollectionFactory $rmaItemCollectionFactory
     * @param \Webkul\Rmasystem\Model\ResourceModel\Shippinglabel\CollectionFactory $labelCollectionFactory
     * @param \Webkul\Rmasystem\Model\ResourceModel\Conversation\CollectionFactory $conversationCollectionFactory
     * @param \Magento\Framework\Registry $registry
     * @param array                                 $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Directory\Model\Currency $currency,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Framework\View\Element\FormKey $formKey,
        ItemCollectionFactory $itemCollectionFactory,
        \Webkul\Rmasystem\Api\Data\RmaitemInterfaceFactory $rmaItemDataFactory,
        \Webkul\Rmasystem\Model\ResourceModel\Shippinglabel\CollectionFactory $labelCollectionDataFactory,
        \Webkul\Rmasystem\Api\RmaitemRepositoryInterface $rmaItemRepository,
        \Webkul\Rmasystem\Api\Data\ConversationInterfaceFactory $conversationDataFactory,
        \Webkul\Rmasystem\Api\ConversationRepositoryInterface $conversationRepository,
        \Webkul\Rmasystem\Api\ReasonRepositoryInterface $reasonRepository,
        OrderRepository $orderRepository,
        AllRmaRepositoryInterface $rmaRepository,
        AllrmaInterfaceFactory $rmaFactory,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Sales\Model\Order\ItemRepository $orderItemRepository,
        \Magento\Customer\Model\ResourceModel\CustomerRepository $customerRepository,
        \Webkul\Rmasystem\Helper\Data $helper,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->itemCollectionFactory = $itemCollectionFactory;
        $this->rmaItemDataFactory = $rmaItemDataFactory;
        $this->rmaItemRepository = $rmaItemRepository;
        $this->conversationDataFactory = $conversationDataFactory;
        $this->conversationRepository = $conversationRepository;
        $this->reasonRepository = $reasonRepository;
        $this->orderRepository = $orderRepository;
        $this->rmaRepository = $rmaRepository;
        $this->rmaFactory = $rmaFactory;
        $this->productRepository = $productRepository;
        $this->orderItemRepository = $orderItemRepository;
        $this->customerRepository = $customerRepository;
        $this->labelCollectionDataFactory = $labelCollectionDataFactory;
        $this->helper = $helper;
        $this->_coreRegistry = $registry;
        $this->_currency = $currency;
        $this->_date = $date;
        $this->_formKey = $formKey;
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * Initialize blog post edit block.
     */
    protected function _construct()
    {
        $this->_objectId = 'rma_id';
        $this->_blockGroup = 'Webkul_Rmasystem';
        $this->_controller = 'adminhtml_allrma';

        parent::_construct();

        if ($this->_isAllowedAction('Webkul_Rmasystem::update')) {
            $this->buttonList->update('save', 'label', __('Update RMA'));
        } else {
            $this->buttonList->remove('save');
        }
        $this->addButton(
            'print',
            [
                'label' => __('Print RMA'),
                'onclick' => 'window.open(\'' . $this->getPrintUrl() . '\',"_blank")',
                'class' => 'scalable print',
                'level' => -1
            ]
        );
        $this->buttonList->remove('delete');
    }

    /**
     * Retrieve text for header element depending on loaded post.
     *
     * @return \Magento\Framework\Phrase
     */
    public function getHeaderText()
    {
        if ($this->_coreRegistry->registry('rmasystem_allrma')->getId()) {
            return __(
                "Edit Post '%1'",
                $this->escapeHtml(
                    $this->_coreRegistry->registry('rmasystem_allrma')->getTitle()
                )
            );
        } else {
            return __('New Rma');
        }
    }

    /**
     * Check permission for passed action.
     *
     * @param string $resourceId
     *
     * @return bool
     */
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }

    /**
     * function to check if credit memo of the requested qty is possible
     *
     * @param \Magento\Sales\Model\Order\ItemRepository $orderItem
     * @param int $requestQty
     * @return boolean
     */
    public function canCreateCreditMemo($orderItem, $requestQty) : bool
    {
        $canCreate = true;
        if ($orderItem->getQtyInvoiced() - $orderItem->getQtyRefunded() >= $requestQty)
            return $canCreate;
        else 
            return false;
    }

    public function getOrder($orderId)
    {
        return $this->orderRepository->get($orderId);
    }

    /**
     * Retrieve url for form submiting.
     *
     * @return string
     */
    public function getUpdateUrl()
    {
        return $this->getUrl('rmasystem/allrma/update');
    }

    public function getPrintUrl()
    {
        $rmaId = $this->getRmaId();
        return $this->getUrl('rmasystem/allrma/printrma', ['id'=>$rmaId]);
    }

    /**
     * Getter of url for "Save and Continue" button
     * tab_id will be replaced by desired by JS later.
     *
     * @return string
     */
    protected function _getSaveAndContinueUrl()
    {
        return $this->getUrl('allrma/*/update', ['_current' => true, 'back' => 'edit', 'active_tab' => '{{tab_id}}']);
    }
    /**
     * @return bool|\Magento\Sales\Model\ResourceModel\Order\Collection
     */
    public function getConvsersationCollection($id)
    {
        return  $collection = $this->conversationDataFactory->create()
            ->getCollection()
            ->addFieldToFilter('rma_id', $id)
            ->setOrder('created_at', 'DESC');
    }

    public function getAllStatus($resolutionType)
    {
        return $this->helper->getAllStatus($resolutionType);
    }

    /**
     * @return int
     */
    public function getRmaId()
    {
        $id = $this->getRequest()->getParam('id');
        return $id;
    }

    public function getRmaItemDetails($rmaId)
    {
        $itemCollection = $this->rmaItemCollectionFactory->create()
          ->addFieldToFilter('rma_id');
    }

    /**
     * @return int
     */
    public function getCustomerDetail($id)
    {
        return $this->customerRepository->getById($id);
    }
    /**
     * @return string
     */
    public function getBaseUrl()
    {
        return $this->helper->getBaseUrl().$this->getRmaId().'/image';
    }
    /**
     * @return string
     */
    public function getBaseDirRead()
    {
        return $this->helper->getBaseDirRead();
    }

    public function getImages()
    {
        $folderName = $this->getBaseDirRead().$this->getRmaId().'/image/';

        $images = \Magento\Framework\Filesystem\Glob::glob(
                    $folderName.'*.{jpg,JPG,jpeg,JPEG,gif,GIF,png,PNG,bmp,BMP}',
                    \Zend\Stdlib\Glob::GLOB_BRACE
                );
        return $images;
    }

    /**
     * @return Mixed \Webkul\Rmasystem\Model\Allrma
     */
    public function getRmaDetail()
    {
        return $this->rmaRepository->getById($this->getRmaId());
    }
    /**
     * @return Mixed
     */
    public function getSalesOrderItemDetail($itemId)
    {
        return $this->orderItemRepository->get($itemId);
    }
    /**
     * @return Mixed \Magento\Sales\Model\Order\Item
     */
    public function getProductDetail($productId)
    {
        try {
            return $this->productRepository->getById($productId);
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            return null;
        }
    }
    /**
     * @return Mixed \Webkul\Rmasystem\Model\Reason
     */
    public function getReason($reasonId)
    {
        return $this->reasonRepository->getById($reasonId);
    }

    /**
     * @return array
     */
    public function getItemCollection($rmaId)
    {
        $collection = $this->rmaItemDataFactory->create()
          ->getCollection()
          ->addFieldToFilter('rma_id', $rmaId);
        return $collection;
    }
    /**
     * @return array
     */
    public function getShippingLabelCollection()
    {
        $collection = $this->labelCollectionDataFactory->create()
          ->addFieldToFilter('status', 1);

        return $collection;
    }
    /**
     * @return string
     */
    public function getLabelBaseUrl()
    {
        return $this->helper->getLabelBaseUrl();
    }
    /**
     * @param Decimal $price
     *
     * @return formated
     */
    public function getCurrency($price)
    {
        return $currency = $this->_currency->format($price);
    }
    /**
     * @param  String Date
     *
     * @return String Timestamp
     */
    public function getTimestamp($date)
    {
        return $date = $this->_date->timestamp($date);
    }

    /**
     * Get form key.
     *
     * @return string
     */
    public function getFormKey()
    {
        return $this->_formKey->getFormKey();
    }
}
