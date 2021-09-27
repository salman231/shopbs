<?php
namespace Webkul\MobikulApi\Controller\RMA;

abstract class AbstractRma extends \Webkul\MobikulApi\Controller\ApiController
{
	protected $_productloader;
	protected $_priceCurrency;
	protected $_customerGroup;
	//protected $scopeConfig;
    protected $_customerRepositoryInterface;
    protected $jsonHelper;
    protected $_order;
    protected $_invoice;
    protected $_creditmemoFactory;
    protected $_creditmemoService;
    //protected $_customerRepository;
    protected $_seller;
    protected $detailsCollection;
    protected $RmaHelper;
    protected $_emailHelper;
    protected $_date;
    protected $orderRepository;
    protected $conversationCollectionFactory;
    protected $conversationDataFactory;
    protected $conversationRepository;
    protected $rmaRepository;
    protected $_orderCollectionFactory;
    protected $_orderShipmentCollectionFactory;
    protected $scopeConfig;
    protected $_reasonCollectionFactory;
    protected $_itemCollectionFactory;
    protected $_allRmaFactory;
    protected $_productRepository;
    protected $_imageHelper;
    protected $rmaFactory;
    protected $resourceModelHelper;
    protected $_fileUploaderFactory;
    protected $rmaItemRepository;
    protected $reasonRepository;
    protected $_file;
    protected $rmaItemDataFactory;
    protected $fieldValue;
    protected $orderItemRepository;
    protected $productRepositoryInt;
	public function __construct(
	   //\Magento\Catalog\Block\Product\Context $productcontext,
      \Magento\Sales\Model\Order $order,
      \Magento\Sales\Model\Order\Invoice $invoice,
      \Magento\Sales\Model\Order\CreditmemoFactory $creditmemoFactory,
      \Magento\Sales\Model\Service\CreditmemoService $creditmemoService,
      //\Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
      \Webkul\Marketplace\Model\Seller $seller,
      \Webkul\MobikulCore\Helper\Data $helper,
      \Magento\Framework\App\Action\Context $context,
      \Magento\Framework\Json\Helper\Data $jsonHelper,
      \Magento\Catalog\Model\ProductFactory $_productloader,
      \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
      \Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface,
      \Magento\Customer\Model\ResourceModel\Group\Collection $customerGroup,
      \Magento\Framework\Controller\ResultFactory $resultFactory,
      \Webkul\Rmasystem\Model\ResourceModel\Allrma\CollectionFactory $detailsCollection,
      \Webkul\Rmasystem\Helper\Data $RmaHelper,
      \Magento\Framework\Stdlib\DateTime\DateTime $date,
      \Magento\Sales\Model\OrderRepository $orderRepository,
      \Webkul\Rmasystem\Model\ResourceModel\Conversation\CollectionFactory $conversationCollectionFactory,
      \Webkul\Rmasystem\Api\Data\ConversationInterfaceFactory $conversationDataFactory,
      \Webkul\Rmasystem\Api\ConversationRepositoryInterface $conversationRepository,
      \Webkul\Rmasystem\Api\AllRmaRepositoryInterface $rmaRepository,
      \Webkul\Rmasystem\Helper\AppEmail $emailhelper,
      \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
      \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
      \Magento\Sales\Model\ResourceModel\Order\Shipment\CollectionFactory $orderShipmentCollectionFactory,
      \Webkul\Rmasystem\Model\ResourceModel\Reason\CollectionFactory $reasonCollectionFactory,
      \Webkul\Rmasystem\Model\ResourceModel\Rmaitem\CollectionFactory $itemCollectionFactory,
      \Webkul\Rmasystem\Model\AllrmaFactory $allRmaFactory,
      \Magento\Catalog\Model\ProductRepository $productRepository,
      \Magento\Catalog\Helper\Image $imageHelper,
      \Webkul\Rmasystem\Api\Data\AllrmaInterfaceFactory $rmaFactory,
      \Magento\ImportExport\Model\ResourceModel\Helper $resourceModelHelper,
      \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory,
      \Webkul\Rmasystem\Api\ReasonRepositoryInterface $reasonRepository,
      \Webkul\Rmasystem\Api\RmaitemRepositoryInterface $rmaItemRepository,
      \Magento\Framework\Filesystem\Io\File $file,
      \Webkul\Rmasystem\Api\Data\RmaitemInterfaceFactory $rmaItemDataFactory,
      \Webkul\Rmasystem\Model\FieldvalueFactory $fieldValueFactory,
      \Magento\Sales\Model\Order\ItemRepository $orderItemRepository,
      \Magento\Catalog\Api\ProductRepositoryInterface $productRepositoryInt,
      \Magento\Store\Model\App\Emulation $emulate
	)
	{
      $this->_order= $order;
      $this->_invoice = $invoice;
      $this->_creditmemoFactory = $creditmemoFactory;
      $this->_creditmemoService = $creditmemoService;
      //$this->_customerRepository = $customerRepository;
      $this->_seller = $seller;
      $this->_productloader = $_productloader;
      $this->_priceCurrency = $priceCurrency;
      $this->_customerGroup = $customerGroup;
      $this->_customerRepositoryInterface = $customerRepositoryInterface;
      $this->_resultFactory = $resultFactory;
      $this->helper = $helper;
      $this->jsonHelper = $jsonHelper;
      $this->detailsCollection = $detailsCollection;
      $this->RmaHelper = $RmaHelper;
      $this->_date = $date;
      $this->orderRepository = $orderRepository;
      $this->conversationCollectionFactory = $conversationCollectionFactory;
      $this->conversationDataFactory = $conversationDataFactory;
      $this->conversationRepository = $conversationRepository;
      $this->rmaRepository = $rmaRepository;
      $this->_emailHelper = $emailhelper;
      $this->_orderCollectionFactory = $orderCollectionFactory;
      $this->emulate = $emulate;
      $this->scopeConfig = $scopeConfig;
      $this->_orderShipmentCollectionFactory = $orderShipmentCollectionFactory;
      $this->_reasonCollectionFactory = $reasonCollectionFactory;
      $this->_itemCollectionFactory = $itemCollectionFactory;
      $this->_allRmaFactory= $allRmaFactory;
      $this->_productRepository = $productRepository;
      $this->_imageHelper = $imageHelper;
      $this->rmaFactory = $rmaFactory;
      $this->resourceModelHelper = $resourceModelHelper;
      $this->_fileUploaderFactory = $fileUploaderFactory;
      $this->reasonRepository = $reasonRepository;
      $this->rmaItemRepository = $rmaItemRepository;
      $this->_file = $file;
      $this->rmaItemDataFactory = $rmaItemDataFactory;
      $this->fieldValue = $fieldValueFactory;
      $this->orderItemRepository = $orderItemRepository;
      $this->productRepositoryInt = $productRepositoryInt;
      //$this->scopeConfig = $productcontext->getScopeConfig();
      parent::__construct($helper, $context, $jsonHelper);
	}
}