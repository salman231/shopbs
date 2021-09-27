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
namespace Webkul\Rmasystem\Controller\Adminhtml\Customfield;

use Magento\Framework\Controller\ResultFactory;
use Magento\Ui\Component\MassAction\Filter;
use Webkul\Rmasystem\Model\CustomfieldFactory;
use Magento\Backend\App\Action\Context;

/**
 * Class Delete
 */
class Delete extends \Magento\Backend\App\Action
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
     *
     * @param Context $context
     * @param Filter $filter
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        Context $context,
        Filter $filter,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        CustomfieldFactory $collectionFactory
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
        $id = $this->getRequest()->getParam('id');

        if ($id) {
            $collection = $this->collectionFactory->create()->load($id);
            $collection->delete();
            $this->messageManager->addSuccess(__('Custom Field deleted successfully.'));
        }
       
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
        return $this->_authorization->isAllowed('Webkul_Rmasystem::customfield');
    }
}
