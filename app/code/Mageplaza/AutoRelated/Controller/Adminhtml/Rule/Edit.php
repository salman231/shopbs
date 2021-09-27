<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_AutoRelated
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\AutoRelated\Controller\Adminhtml\Rule;

use Magento\Backend\Model\View\Result\Page;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Mageplaza\AutoRelated\Controller\Adminhtml\Rule;

/**
 * Class Edit
 * @package Mageplaza\AutoRelated\Controller\Adminhtml\Rule
 */
class Edit extends Rule
{
    /**
     * @return Page|ResponseInterface
     */
    public function execute()
    {
        $id        = $this->getRequest()->getParam('id');
        $type      = $this->getRequest()->getParam('type');
        $model     = $this->autoRelatedRuleFactory->create();
        $ruleModel = $this->autoRelatedRuleFactory->create();
        if ($id) {
            try {
                $model->load($id);
                if ($model->getBlockType() != $type) {
                    $this->messageManager->addErrorMessage(__('Something went wrong.'));

                    return $this->_redirect('mparp/*');
                }
            } catch (NoSuchEntityException $exception) {
                $this->messageManager->addErrorMessage(__('This rule no longer exists.'));
                $this->_redirect('mparp/*');

                return;
            }
        }

        if ($this->coreRegistry->registry('autorelated_test_add') && (!$id || $model->hasChild() || $model->getParentId())) {
            $this->messageManager->addErrorMessage(__('Can not Add A/B Testing.'));

            return $this->_redirect('mparp/*');
        }

        // set entered data if was error when we do save
        $data = $this->_session->getPageData(true);
        if (!empty($data)) {
            $model->addData($data);
        }

        $this->coreRegistry->register('autorelated_rule_category', $model->getCategoryConditionsSerialized());
        $this->coreRegistry->register('autorelated_rule', $model);
        $this->coreRegistry->register('autorelated_type', $type);
        $this->coreRegistry->register('autoRelated_type_product', $ruleModel->load($id));

        /** @var Page $resultPage */
        $resultPage = $this->_initAction();
        $resultPage->getConfig()->getTitle()->prepend($id ? $model->getName() : __('New Related Block Rule'));

        $title = $id ? __('Edit Rule') : __('New Rule');
        $this->_addBreadcrumb($title, $title);

        return $resultPage;
    }
}
