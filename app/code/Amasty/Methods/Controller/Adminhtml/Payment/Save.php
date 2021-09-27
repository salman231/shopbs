<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Methods
 */

/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Amasty\Methods\Controller\Adminhtml\Payment;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Save extends \Magento\Backend\App\Action
{
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Amasty_Methods::methods_payment');
    }

    public function execute()
    {
        if ($this->getRequest()->getPostValue()) {
            $data = $this->getRequest()->getPostValue();

            try {
                $model = $this->_objectManager->create('Amasty\Methods\Model\Structure\Payment');

                $model->save($data);

                $this->messageManager->addSuccess(__('You saved the payment methods visibility.'));

                $this->_redirect('amasty_methods/*/index', [
                    'website_id' => $model->getId()
                ]);
                return;
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
                $id = (int)$this->getRequest()->getParam('website_id');
                if (!empty($id)) {
                    $this->_redirect('amasty_methods/*/index', ['website_id' => $id]);
                } else {
                    $this->_redirect('amasty_methods/*/index');
                }
                return;
            } catch (\Exception $e) {
                $this->messageManager->addError(
                    __('Something went wrong while saving the payment methods visibility data. Please review the error log.')
                );
                $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
                $this->_objectManager->get('Magento\Backend\Model\Session')->setPageData($data);
                $this->_redirect('amasty_methods/*/index', ['website_id' => $this->getRequest()->getParam('website_id')]);
                return;
            }
        }
        $this->_redirect('amasty_methods/*/index');
    }
}
