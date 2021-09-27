<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Customform
 */


namespace Amasty\Customform\Controller\Adminhtml\Answer;

use Amasty\Customform\Api\Data\AnswerInterface;

class Index extends \Amasty\Customform\Controller\Adminhtml\Answer
{
    public function execute()
    {
        if ($formId = (int)$this->getRequest()->getParam('form_id', null)) {
            $this->bookmark->applyFilter(
                'amasty_customform_answer_listing',
                [
                    'form_id' => $formId,
                    AnswerInterface::ADMIN_RESPONSE_STATUS => $this->getRequest()->getParam('status', null)
                ]
            );
            $this->bookmark->clear();
        }

        $this->_initAction();
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Submitted Data'));
        $this->_view->renderLayout();
    }
}
