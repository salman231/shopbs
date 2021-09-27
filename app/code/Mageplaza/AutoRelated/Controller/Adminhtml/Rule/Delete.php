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

use Exception;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Mageplaza\AutoRelated\Controller\Adminhtml\Rule;

/**
 * Class Delete
 * @package Mageplaza\AutoRelated\Controller\Adminhtml\Rule
 */
class Delete extends Rule
{
    /**
     * @return ResponseInterface|ResultInterface|void
     */
    public function execute()
    {
        if ($id = $this->getRequest()->getParam('id')) {
            try {
                $rule = $this->autoRelatedRuleFactory->create()
                    ->load($id)
                    ->delete();

                $child = $rule->getChild();
                if ($child['rule_id']) {
                    $this->autoRelatedRuleFactory->create()
                        ->load($child['rule_id'])
                        ->delete();
                }

                $this->messageManager->addSuccessMessage(__('You deleted the rule.'));
                $this->_redirect('mparp/*/');

                return;
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (Exception $e) {
                $this->messageManager->addErrorMessage(
                    __('We can\'t delete this rule right now. Please review the log and try again.')
                );
                $this->logger->critical($e);
                $this->_redirect('mparp/*/');

                return;
            }
        }
        $this->messageManager->addErrorMessage(__('We can\'t find a rule to delete.'));
        $this->_redirect('mparp/*/');
    }
}
