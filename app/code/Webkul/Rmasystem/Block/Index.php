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
namespace Webkul\Rmasystem\Block;

use Magento\Framework\Session\SessionManager;
use Webkul\Rmasystem\Helper\Filter;

/**
 * Customer Rma list block
 */
class Index extends \Magento\Framework\View\Element\Template
{

    /**
     * @var \Webkul\Rmasystem\Model\ResourceModel\Allrma\CollectionFactory
     */
    protected $rmaCollectionFactory;

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

    /** @var \Webkul\Rmasystem\Model\Allrma */
    protected $rma;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_date;

     /**
      * @var \Magento\Framework\ObjectManagerInterface
      */
    protected $_objectManager = null;

    /**
     * @param \Magento\Framework\View\Element\Template\Context               $context
     * @param \Magento\Customer\Model\Session                                $customerSession
     * @param \Magento\Framework\Stdlib\DateTime\DateTime                    $date
     * @param SessionManager $session,
     * @param \Magento\Sales\Model\Order\Config                              $orderConfig
     * @param \Webkul\Rmasystem\Model\ResourceModel\Allrma\CollectionFactory $rmaCollectionFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        SessionManager $session,
        Filter $filterSorting,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Webkul\Rmasystem\Model\ResourceModel\Allrma\CollectionFactory $rmaCollectionFactory,
        array $data = []
    ) {
        $this->rmaCollectionFactory = $rmaCollectionFactory;
        $this->_customerSession = $customerSession;
        $this->session = $session;
        $this->_date = $date;
        $this->filterSorting = $filterSorting;
        $this->_objectManager = $objectManager;
        parent::__construct($context, $data);
    }

    /**
     * @return void
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
        if (!($customerId = $this->_customerSession->getCustomerId())) {
            return false;
        }
        $this->session->unsNewRmaSortingSession();
        $this->session->getNewRmaFilterData();
        if (!$this->rma) {
            $collection = $this->rmaCollectionFactory->create()
            ->addFieldToSelect(
                '*'
            )->addFieldToFilter(
                'customer_id',
                $customerId
            );

            $sortingData = $this->getSortingSession();

            $filterData = $this->getFilterData();

            if ($filterData["order_id"] != "") {
                $collection->addFieldToFilter("increment_id", ['like' => '%'.$filterData["order_id"].'%']);
            }
            if ($filterData["status"] != "") {
                $collection->addFieldToFilter("status", $filterData["status"]);
            }
            if ($filterData["rma_id"] != "") {
                $collection->addFieldToFilter("rma_id", $filterData["rma_id"]);
            }
            if ($filterData["date"] != "") {
                $collection->addFieldToFilter("created_at", ["gteq" => $filterData["date"]." 00:00:00"]);
                $collection->addFieldToFilter("created_at", ["lteq" => $filterData["date"]." 23:59:59"]);
            }
            if ($sortingData["attr"] != "" && $sortingData["direction"] != "") {
                $collection->setOrder($sortingData["attr"], $sortingData["direction"]);
            } else {
                $collection->setOrder('rma_id', 'desc');
            }
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
                'webkul.rmasystem.index.pager'
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
        return $this->filterSorting->getSortingSession();
    }

    /**
     * @return array
     */
    public function getFilterData()
    {
        return $this->filterSorting->getFilterSession();
    }

    /**
     *
     * @param  String Date
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
