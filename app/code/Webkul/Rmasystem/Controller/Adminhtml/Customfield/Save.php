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

use Magento\Backend\App\Action;

class Save extends \Magento\Backend\App\Action
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    protected $_customField;

    /**
     * @param Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\Registry $registry
     */
    public function __construct(
        Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Webkul\Rmasystem\Model\CustomfieldFactory $fieldFactory,
        \Magento\Framework\Registry $registry
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->_customField = $fieldFactory;
        $this->_coreRegistry = $registry;
        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Webkul_Rmasystem::customfield');
    }

    /**
     * Edit Blog post
     *
     * @return \Magento\Backend\Model\View\Result\Page|\Magento\Backend\Model\View\Result\Redirect
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        // print_r($this->getRequest()->getParams()); die;;
        $resultRedirect = $this->resultRedirectFactory->create();
        $data = $this->getRequest()->getParams();
        try {
            if ($data) {
                $id = $this->getRequest()->getParam('id');
                $field = $this->_customField->create();
                if ($id) {
                    $field->setId($id);
                }
                $field->setData($data);
                $id = $field->save()->getId();
                $this->messageManager->addSuccess(__('Field saved successfully.'));
                $this->_objectManager->get('Magento\Backend\Model\Session')->setFormData(false);
                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['id' => $id, '_current' => true]);
                }
                return $resultRedirect->setPath('*/*/');
            }
        } catch (\Exception $e) {
            $this->messageManager->addException($e, __('Something went wrong while saving the Field.'));
            return $resultRedirect->setPath('*/*/');
        }
    }
}
