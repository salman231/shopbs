<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Customform
 */


namespace Amasty\Customform\Block\Widget\Form\Element;

class Textinput extends AbstractElement
{
    public function _construct()
    {
        parent::_construct();
        $this->options['title'] = __('Text Input');
    }

    public function generateContent()
    {
        return '<input class="form-control" type="text"/>';
    }
}
