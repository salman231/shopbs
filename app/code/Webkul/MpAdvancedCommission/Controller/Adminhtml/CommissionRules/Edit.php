<?php
/**
 * @category   Webkul
 * @package    Webkul_MpAdvancedCommission
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\MpAdvancedCommission\Controller\Adminhtml\CommissionRules;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Edit extends Action
{
    /**
     * Core registry.
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $_resultPageFactory;

    /**
     * @param Context     $context
     * @param PageFactory $_resultPageFactory
     */
    public function __construct(
        Context $context,
        \Magento\Framework\Registry $registry,
        PageFactory $resultPageFactory,
        \Webkul\MpAdvancedCommission\Model\CommissionRulesFactory $commissionRule
    ) {
    
        parent::__construct($context);
        $this->_resultPageFactory = $resultPageFactory;
        $this->_coreRegistry = $registry;
        $this->_commissionRule = $commissionRule;
    }

    /**
     * Init actions.
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    protected function _initAction()
    {
        // load layout, set active menu and breadcrumbs
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->_resultPageFactory->create();
        $resultPage->setActiveMenu(
            'Webkul_MpAdvancedCommission::commissionrules'
        )
        ->addBreadcrumb(
            __('Commission Rules'),
            __('Commission Rules')
        )
        ->addBreadcrumb(
            __('Manage Commission Rules'),
            __('Manage Commission Rules')
        );

        return $resultPage;
    }

    /**
     * MpAdvancedCommission Commission Rules Edit page.
     *
     * @return \Magento\Backend\Model\View\Result\Page|\Magento\Backend\Model\View\Result\Redirect
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('rule_id');
        $model = $this->_commissionRule->create();
        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                $this->messageManager->addError(__('This commission rule no longer exists.'));
                /** \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
                $resultRedirect = $this->resultRedirectFactory->create();

                return $resultRedirect->setPath('*/*/');
            }
        }

        $data = $this->_objectManager->get(\Magento\Backend\Model\Session::class)->getFormData(true);
        if (!empty($data)) {
            $model->setData($data);
        }

        $this->_coreRegistry->register('commission_rules', $model);

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->_initAction();
        $resultPage->addBreadcrumb(
            $id ? __('Edit Commission Rules') : __('Add New Commission Rules'),
            $id ? __('Edit Commission Rules') : __('Add New Commission Rules')
        );
        $resultPage->getConfig()->getTitle()->prepend(__('Manage Commission Rules'));
        $resultPage->getConfig()->getTitle()
        ->prepend(
            $model->getId() ? __(
                "Edit Commission Rules for id '%1'",
                $model->getRuleId()
            ) : __('Add New Commission Rules')
        );

        return $resultPage;
    }

    /**
     * Check for is allowed.
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed(
            'Webkul_MpAdvancedCommission::commissionrules'
        );
    }
}
