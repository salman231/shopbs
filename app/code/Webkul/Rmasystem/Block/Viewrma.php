<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Rmasystem
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Rmasystem\Block;

use Magento\Framework\Session\SessionManager;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Webkul\Rmasystem\Model\ResourceModel\Rmaitem\CollectionFactory as RmaitemCollectionFactory;
use Webkul\Rmasystem\Model\ResourceModel\Conversation\CollectionFactory as ConversationCollectionFactory;
use Webkul\Rmasystem\Model\ResourceModel\Allrma\CollectionFactory as RmaCollectionFactory;
use Magento\Framework\UrlInterface;
use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Sales\Model\OrderRepository;

/**
 * Customer View RMA.
 */
class Viewrma extends \Magento\Framework\View\Element\Template
{
    /**
     * media sub folder
     * @var string
     */
    protected $subDir = 'webkul/rmasystem/RMA';
    /**
     * media sub folder
     * @var string
     */
    protected $labelDir = 'webkul/rmasystem/shippinglabel/image';

    /**
     * @var \Magento\Directory\Model\Currency;
     */
    protected $_currency;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime;
     */
    protected $_date;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Framework\Data\Form\FormKey
     */
    protected $_formKey;

    /**
     * @var \Webkul\Rmasystem\Model\ResourceModel\Conversation\Collection
     */
    protected $conversation;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager = null;

    /**
     * @var RmaitemCollectionFactory
     */
    protected $rmaItemCollectionFactory;

    /**
     * @var ConversationCollectionFactory
     */
    protected $conversationCollectionFactory;

    /**
     * @var \Webkul\Rmasystem\Api\Data\ReasonRepositoryInterface
     */
    protected $reasonRepository;

    /**
     * @var \Webkul\Rmasystem\Api\ShippingLabelRepositoryInterface
     */
    protected $shippingLabelRepository;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var \Magento\Sales\Model\Order\ItemRepository
     */
    protected $orderItemRepository;

    /**
     * @var RmaCollectionFactory
     */
    protected $rmaCollectionFactory;

    /**
     * @var \Magento\Catalog\Helper\Image
     */
    protected $_imageHelper;

    /**
     * @var OrderRepository
     */
    protected $orderRepository;

    /**
     * @param \Magento\Framework\View\Element\Template\Context           $context
     * @param \Magento\Catalog\Block\Product\Context                     $productContext
     * @param \Magento\Customer\Model\Session                            $customerSession
     * @param \Magento\Directory\Model\Currency                          $currency
     * @param \Magento\Framework\Stdlib\DateTime\DateTime                $date
     * @param RmaitemCollectionFactory                                   $rmaItemCollectionFactory
     * @param ConversationCollectionFactory                              $conversationCollectionFactory
     * @param UrlInterface                                               $urlBuilder
     * @param Filesystem                                                 $fileSystem
     * @param array                                                      $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Catalog\Block\Product\Context $productContext,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Directory\Model\Currency $currency,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        RmaCollectionFactory $rmaCollectionFactory,
        RmaitemCollectionFactory $rmaItemCollectionFactory,
        ConversationCollectionFactory $conversationCollectionFactory,
        \Webkul\Rmasystem\Api\ReasonRepositoryInterface $reasonRepository,
        \Webkul\Rmasystem\Api\ShippingLabelRepositoryInterface $shippingLabelRepository,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Sales\Model\Order\ItemRepository $orderItemRepository,
        OrderRepository $orderRepository,
        array $data = []
    ) {

        $this->rmaItemCollectionFactory = $rmaItemCollectionFactory;
        $this->orderRepository = $orderRepository;
        $this->conversationCollectionFactory = $conversationCollectionFactory;
        $this->reasonRepository = $reasonRepository;
        $this->shippingLabelRepository = $shippingLabelRepository;
        $this->productRepository = $productRepository;
        $this->orderItemRepository = $orderItemRepository;
        $this->rmaCollectionFactory = $rmaCollectionFactory;
        $this->_customerSession = $customerSession;
        $this->_currency = $currency;
        $this->_date = $date;
        $this->fileSystem = $context->getFilesystem();
        $this->_imageHelper = $productContext->getImageHelper();
        parent::__construct($context, $data);
    }

    /**
     */
    protected function _construct()
    {
        parent::_construct();
        $this->pageConfig->getTitle()->set(__('RMA Details'));
    }

    /**
     * @return bool|\Magento\Sales\Model\ResourceModel\Order\Collection
     */
    public function getRmaCollection($id)
    {
        if (!($customerId = $this->_customerSession->getCustomerId())) {
            return false;
        }

        if (!$this->conversation) {
            $collection = $this->conversationCollectionFactory->create()
              ->addFieldToFilter('rma_id', $id)
              ->setOrder('created_at', 'DESC');
            $this->conversation = $collection;
        }
        return $this->conversation;
    }
    /**
     * @return string
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    /**
     * @return int
     */
    public function getRmaId()
    {
        $id = $this->getRequest()->getParam('id');

        return $id;
    }
    /**
     * @return int
     */
    public function getCustomerId()
    {
        return $this->_customerSession->getCustomerId();
    }
    /**
     * @return string
     */
    public function getLabelBaseUrl()
    {
        $baseUrl = $this->_urlBuilder->getBaseUrl(['_type' => UrlInterface::URL_TYPE_MEDIA]).$this->labelDir;
        return $baseUrl;
    }
    /**
     * @return string
     */
    public function getBaseUrl()
    {
        $baseUrl = $this->_urlBuilder->getBaseUrl(
            ['_type' => UrlInterface::URL_TYPE_MEDIA]
        ).$this->subDir.'/';
        return $baseUrl.$this->getRmaId().'/image';
    }
    /**
     * @return string
     */
    public function getBaseDirRead()
    {
        $directory = $this->fileSystem->getDirectoryRead(
            DirectoryList::MEDIA
        )->getAbsolutePath($this->subDir.'/');
        return $directory;
    }
    /**
     * @return string
     */
    public function getBarBaseUrl()
    {
        return $this->_urlBuilder->getBaseUrl(
            ['_type' => UrlInterface::URL_TYPE_MEDIA]
        ).$this->subDir.'/Barcodes/';
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
     * @return Mixed
     */
    public function getRmaDetail()
    {
        $id = $this->getRequest()->getParam('id');

        $collection = $this->rmaCollectionFactory->create()
            ->addFieldToFilter('customer_id', ['eq' => $this->getCustomerId()])
            ->addFieldToFilter('rma_id', ['eq' => $id]);

        if ($collection->getSize()) {
            foreach ($collection as $value) {
                return $value;
            }
        } else {
            return false;
        }
    }
    /**
     * @return \Webkul\Rmasystem\Api\Data\ShippinglabelInterface
     */
    public function getRmaShippingLabelModel($id)
    {
        return $this->shippingLabelRepository->getById($id);
    }
    /**
     * @return Mixed
     */
    public function getSalesOrderItemDetail($itemId)
    {
        return $this->orderItemRepository->get($itemId);
    }

    /**
     * @return Magento\Catalog\Api\Data\ProductInterface
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
     * @return \Webkul\Rmasystem\Api\Data\ReasonInterface
     */
    public function getReason($reasonId)
    {
        return $this->reasonRepository->getById($reasonId);
    }

    /**
     * @return array
     */
    public function getItemCollection($rma_id)
    {
        $collection = $this->rmaItemCollectionFactory->create()
          ->addFieldToFilter('rma_id', $rma_id);

        return $collection;
    }

    /**
     * get current order related to RMA
     * @param  int $orderId
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder($orderId)
    {
        return $this->orderRepository->get($orderId);
    }

    /**
     * @return float
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
     * @return string
     */
    public function getBackUrl()
    {
        return $this->getUrl('customer/account/');
    }

    public function imageHelperObj()
    {
        return $this->_imageHelper;
    }
}
