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
 * @package     Mageplaza_DailyDeal
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\DailyDeal\Controller\Adminhtml\Deal;

use Exception;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;
use Mageplaza\DailyDeal\Controller\Adminhtml\Deal;

/**
 * Class Delete
 * @package Mageplaza\DailyDeal\Controller\Adminhtml\Deal
 */
class Delete extends Deal
{
    /**
     * @return ResponseInterface|Redirect|ResultInterface
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        try {
            $this->_dealFactory->create()
                ->load($this->getRequest()->getParam('id'))
                ->delete();
            $this->messageManager->addSuccessMessage(__('The Deal has been deleted.'));
        } catch (Exception $e) {
            // display error message
            $this->messageManager->addErrorMessage($e->getMessage());
            // go back to edit form
            $resultRedirect->setPath('*/*/edit', ['id' => $this->getRequest()->getParam('id')]);

            return $resultRedirect;
        }

        $resultRedirect->setPath('*/*/');

        return $resultRedirect;
    }
}
