<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Customform
 */

/**
 * Copyright В© 2016 Amasty. All rights reserved.
 */
namespace Amasty\Customform\Block\Widget\Form\Element;

class Checkbox extends AbstractElement
{
    public function _construct()
    {
        parent::_construct();
        $this->options['title'] = __('Checkbox v.1');
        $this->options['image_href'] = 'Amasty_Customform::images/checkbox.png';
    }

    public function generateContent()
    {
        return '<input value="option1" type="checkbox" name="checkbox1[] id="checkbox1">
            <label ' . $this->getLabelClassName() . ' for="checkbox1">'. __('Checkbox unselected') . '</label>
            ' . $this->getBr() . '
            <input value="option-345345" type="Checkbox" name="checkbox1[]" checked id="checkbox11"> 
            <label ' . $this->getLabelClassName() . ' for="checkbox11">'. __('Checkbox selected') . '</label>';
    }

    public function getLabelClassName()
    {
        return '';
    }

    public function getBr()
    {
        return '<br>';
    }
}
