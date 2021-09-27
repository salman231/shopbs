<?php
/**
 * Dart Productkeys List Controller.
 * @package   Dart_Productkeys
 *
 */
namespace Dart\Productkeys\Controller\Adminhtml\Productkeys;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Dart\Productkeys\Model\ProductkeysFactory;
use Magento\Framework\Controller\ResultFactory;
 
class AddRow extends Action
{
    /**
     * @var \Magento\Framework\Registry
     */
    private $coreRegistry;

    /**
     * @var \Dart\Productkeys\Model\ProductkeysFactory
     */
    private $productkeysFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context,
     * @param \Magento\Framework\Registry $coreRegistry,
     * @param \Dart\Productkeys\Model\ProductkeysFactory $productkeysFactory
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        ProductkeysFactory $productkeysFactory
    ) {
        parent::__construct($context);
        $this->coreRegistry = $coreRegistry;
        $this->productkeysFactory = $productkeysFactory;
    }

    /**
     * Mapped Grid List page.
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $rowId = (int) $this->getRequest()->getParam('id');
        $rowData = $this->productkeysFactory->create();
        if ($rowId) {
            $rowData = $rowData->load($rowId);
            $rowTitle = $rowData->getTitle();
            if (!$rowData->getId()) {
                $this->messageManager->addError(__('Row data no longer exist.'));
                $this->_redirect('productkeys/productkeys/index');
                return;
            }
        }

        $this->coreRegistry->register('row_data', $rowData);
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->setActiveMenu('Dart_Productkeys::add_row');
        $title = $rowId ? __('Edit Product Key ').$rowTitle : __('Add Product Key');
        $resultPage->getConfig()->getTitle()->prepend($title);
        return $resultPage;
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Dart_Productkeys::add_row');
    }
}
