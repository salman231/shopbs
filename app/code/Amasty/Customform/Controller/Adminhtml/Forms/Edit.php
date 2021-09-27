<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Customform
 */


namespace Amasty\Customform\Controller\Adminhtml\Forms;

class Edit extends \Amasty\Customform\Controller\Adminhtml\Forms
{
    /**
     * Init actions
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    protected function _initAction()
    {
        // load layout, set active menu and breadcrumbs
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu(self::ADMIN_RESOURCE_PAGE)
            ->addBreadcrumb(__('Custom Forms'), __('Custom Forms'))
            ->addBreadcrumb(__('Manage Form'), __('Manage Form'));

        return $resultPage;
    }

    public function execute()
    {
        $id = $this->getRequest()->getParam('form_id');
        /** @var \Amasty\Customform\Model\Form $model */
        $model = $this->formFactory->create();

        // 2. Initial checking
        if ($id) {
            $model = $this->formRepository->get($id);
            if (!$model->getId()) {
                $this->messageManager->addErrorMessage(__('This form no longer exists.'));
                /** \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
                $resultRedirect = $this->resultRedirectFactory->create();

                return $resultRedirect->setPath('*/*/');
            }
        }

        $data = $this->_session->getFormData(true);

        if (!empty($data)) {
            $model->setData($data);
        }

        $this->_coreRegistry->register('amasty_customform_form', $model);

        $resultPage = $this->_initAction();
        $resultPage->addBreadcrumb(
            $id ? __('Edit Custom Form') : __('New Custom Form'),
            $id ? __('Edit Custom Form') : __('New Custom Form')
        );
        $resultPage->getConfig()->getTitle()->prepend(__('Custom Forms'));
        $resultPage->getConfig()->getTitle()
            ->prepend($model->getId() ? __('Edit Form ') . $model->getCode() : __('New Custom Form'));

        return $resultPage;
    }
}
