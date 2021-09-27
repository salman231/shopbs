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

class Radio extends AbstractElement
{
    public function _construct()
    {
        parent::_construct();

        $this->options['title'] = __('Radio v.1');
        $this->options['image_href'] = 'Amasty_Customform::images/radio-button.png';
    }

    public function generateContent()
    {
        return '<input value="option1" type="radio" name="radio1[] id="radio1">
            <label ' . $this->getLabelClassName() . ' for="radio1">'. __('Radio button unselected') . '</label>
            ' . $this->getBr() . '
            <input value="option-345345" type="radio" name="radio1[]" checked id="radio11"> 
            <label ' . $this->getLabelClassName() . ' for="radio11">'. __('Radio button selected') . '</label>';
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
