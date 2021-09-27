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

namespace Mageplaza\AutoRelated\Controller\Adminhtml\Condition;

use Mageplaza\AutoRelated\Controller\Adminhtml\ConditionAction;

/**
 * Class NewActionHtml
 * @package Mageplaza\AutoRelated\Controller\Adminhtml\Condition
 */
class NewActionHtml extends ConditionAction
{
    /**
     * @return void
     */
    public function execute()
    {
        $id      = $this->getRequest()->getParam('id');
        $typeArr = explode('|', str_replace('-', '/', $this->getRequest()->getParam('type')));
        $type    = $typeArr[0];

        $model = $this->_objectManager->create($type)
            ->setId($id)
            ->setType($type)
            ->setRule($this->_objectManager->create('Mageplaza\AutoRelated\Model\Rule'))
            ->setPrefix('actions');

        if (!empty($typeArr[1])) {
            $model->setAttribute($typeArr[1]);
        }
        $model->setJsFormObject($this->getRequest()->getParam('form'));
        $html = $model->asHtmlRecursive();
        $this->getResponse()->setBody($html);
    }
}
