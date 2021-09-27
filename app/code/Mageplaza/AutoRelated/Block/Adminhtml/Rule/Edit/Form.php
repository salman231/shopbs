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

namespace Mageplaza\AutoRelated\Block\Adminhtml\Rule\Edit;

use Magento\Backend\Block\Widget\Form\Generic;

/**
 * Class Form
 * @package Mageplaza\AutoRelated\Block\Adminhtml\Rule\Edit
 */
class Form extends Generic
{
    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();

        $this->setId('autorelated_rule_form');
        $this->setTitle(__('Rule Information'));
    }

    /**
     * @inheritdoc
     */
    protected function _prepareForm()
    {
        $type = $this->_coreRegistry->registry('autorelated_type');

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create(
            [
                'data' => [
                    'id'     => 'edit_form',
                    'action' => $this->getUrl('mparp/rule/save', [
                        'type' => $type,
                        'test' => $this->_coreRegistry->registry('autorelated_test_add')
                    ]),
                    'method' => 'post',
                ],
            ]
        );

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
