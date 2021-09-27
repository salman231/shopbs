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
namespace Webkul\Rmasystem\Block\Guest;

use Magento\Framework\Session\SessionManager;
use Webkul\Rmasystem\Helper\Filter;

/**
 * Guest Rma list block.
 */
class Rmalist extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Webkul\Rmasystem\Model\ResourceModel\Allrma\CollectionFactory
     */
    protected $_rmaCollectionFactory;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;
    /**
     * @var Session
     */
    protected $session;
    /**
     * @var Webkul\Rmasystem\Helper\Filter
     */
    protected $filterSorting;
    /**
     * @var \Magento\Sales\Model\Order\Config
     */
    protected $_orderConfig;

    /**
     * @var \Webkul\Rmasystem\Model\ResourceModel\Allrma\Collection
     */
    protected $rma;
    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_date;

    protected $_objectManager = null;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * @param \Magento\Framework\View\Element\Template\Context           $context
     * @param \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory
     * @param \Magento\Customer\Model\Session                            $customerSession
     * @param \Magento\Sales\Model\Order\Config                          $orderConfig
     * @param array                                                      $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        SessionManager $session,
        Filter $filterSorting,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Registry $coreRegistry,
        \Webkul\Rmasystem\Model\ResourceModel\Allrma\CollectionFactory $rmaCollectionFactory,
        array $data = []
    ) {
        $this->_rmaCollectionFactory = $rmaCollectionFactory;
        $this->_customerSession = $customerSession;
        $this->session = $session;
        $this->_date = $date;
        $this->filterSorting = $filterSorting;
        $this->coreRegistry = $coreRegistry;
        $this->_objectManager = $objectManager;
        parent::__construct($context, $data);
    }

    /**
     */
    protected function _construct()
    {
        parent::_construct();
        $this->pageConfig->getTitle()->set(__('Rma History'));
    }

    /**
     * @return bool|\Magento\Sales\Model\ResourceModel\Order\Collection
     */
    public function getAllrma()
    {
        $sessionData = $this->_objectManager->create(
            \Magento\Framework\Session\SessionManager::class
        )->getGuestData();
        if (empty($sessionData)) {
            $sessionData = $this->coreRegistry->registry('guest_data');
        }
        
        if (!$this->rma) {
            $collection = $this->_objectManager->create(
                \Webkul\Rmasystem\Model\Allrma::class
            )->getCollection()->addFieldToSelect(
                '*'
            )->addFieldToFilter(
                'guest_email',
                $sessionData['email']
            );
            $sortingData = $this->filterSorting->getGuestSortingSession();
            if ($sortingData['attr'] != '' && $sortingData['direction'] != '') {
                $collection->setOrder($sortingData['attr'], $sortingData['direction']);
            }

            $filtergData = $this->filterSorting->getGuestFilterSession();

            if ($filtergData['order_id'] != '') {
                $collection->addFieldToFilter("increment_id", ['like' => '%'.$filtergData["order_id"].'%']);
            }
            if ($filtergData['status'] != '') {
                $collection->addFieldToFilter('status', $filtergData['status']);
            }
            if ($filtergData['rma_id'] != '') {
                $collection->addFieldToFilter('rma_id', $filtergData['rma_id']);
            }
            if ($filtergData['date'] != '') {
                $collection->addFieldToFilter('created_at', ['gteq' => $filtergData['date'].' 00:00:00']);
            }
            $collection->setOrder('rma_id', 'desc');

            $this->rma = $collection;
        }

        return $this->rma;
    }

    /**
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->getAllrma()) {
            $pager = $this->getLayout()->createBlock(
                \Magento\Theme\Block\Html\Pager::class,
                'webkul.rmasystem.guestlist.pager'
            )->setCollection(
                $this->getAllrma()
            );
            $this->setChild('pager', $pager);
            $this->getAllrma()->load();
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    /**
     * @return array
     */
    public function getSortingSession()
    {
        return $this->filterSorting->getGuestSortingSession();
    }
    /**
     * @return array
     */
    public function getFilterData()
    {
        return $this->filterSorting->getGuestFilterSession();
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
}
