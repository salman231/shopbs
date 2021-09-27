<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Customform
 */


namespace Amasty\Customform\Controller\Adminhtml\Answer;

class Delete extends \Amasty\Customform\Controller\Adminhtml\Answer
{
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        if ($id) {
            try {
                $this->answerRepository->deleteById($id);
                $this->messageManager->addSuccessMessage(__('Answer was deleted.'));
                $this->_redirect('amasty_customform/answer/index');

                return;
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(
                    __('Can\'t delete item right now. Please review the log and try again.')
                );
                $this->logger->critical($e);
                $this->_redirect('amasty_customform/*/edit', ['id' => $id]);

                return;
            }
        }
        $this->messageManager->addErrorMessage(__('Can\'t find a item to delete.'));
        $this->_redirect('amasty_customform/answer/index');
    }
}
