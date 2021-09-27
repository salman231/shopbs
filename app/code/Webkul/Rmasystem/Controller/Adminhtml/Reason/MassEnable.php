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
namespace Webkul\Rmasystem\Controller\Adminhtml\Reason;

use Magento\Framework\Controller\ResultFactory;
use Magento\Ui\Component\MassAction\Filter;
use Webkul\Rmasystem\Model\ResourceModel\Reason\CollectionFactory;
use Magento\Backend\App\Action\Context;

/**
 * Class MassEnable
 */
class MassEnable extends \Magento\Backend\App\Action
{
    /**
     * @var Filter
     */
    protected $_filter;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;
    /**
     * Post enable status
     *
     * @var boolean
     */
    protected $_status = true;
    /**
     * @param Context $context
     * @param Filter $filter
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        Context $context,
        Filter $filter,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        CollectionFactory $collectionFactory
    ) {
    
        $this->_filter = $filter;
        $this->collectionFactory = $collectionFactory;
        parent::__construct($context);
        $this->_storeManager = $storeManager;
    }
     /**
      * Execute action
      *
      * @return \Magento\Backend\Model\View\Result\Redirect
      * @throws \Magento\Framework\Exception\LocalizedException|\Exception
      */
    public function execute()
    {
        $collection = $this->_filter->getCollection($this->collectionFactory->create());
        foreach ($collection as $item) {
            $item->setStatus($this->_status);
            $item->save();
        }
        $this->messageManager->addSuccess(
            __('A total of %1 record(s) have been enabled.', $collection->getSize())
        );

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('*/*/');
    }
    /**
     * Check for is allowed
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Webkul_Rmasystem::reason');
    }
}
