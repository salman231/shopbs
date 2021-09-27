<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Customform
 */


namespace Amasty\Customform\Controller\Adminhtml\Answer;

use Amasty\Customform\Controller\Adminhtml\Answer;
use Magento\Framework\Exception\NoSuchEntityException;

class Edit extends Answer
{
    protected $_publicActions = ['edit'];

    public function execute()
    {
        $id = (int)$this->getRequest()->getParam('id');
        try {
            if (!$id) {
                throw new NoSuchEntityException(__('Response was not found.'));
            }
            $model = $this->answerRepository->get($id);

        } catch (NoSuchEntityException $exception) {
            $this->messageManager->addErrorMessage(__('This Response no longer exists.'));
            $this->_redirect('amasty_customform/answer/index');
            return;
        }

        $this->coreRegistry->register(Answer::CURRENT_ANSWER_MODEL, $model);

        $this->_initAction();
        $this->_view->getPage()->getConfig()->getTitle()->prepend(
            __('Submitted Data #') . $model->getAnswerId()
        );

        $this->_view->renderLayout();
    }
}
