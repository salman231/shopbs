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

class Dropdown extends AbstractElement
{
    public function _construct()
    {
        parent::_construct();
        $this->options['title'] = __('DropDown');
        $this->options['image_href'] = 'Amasty_Customform::images/dropdown.png';
    }

    public function generateContent()
    {
        return '<select><option value="">' . $this->getTestOptionText() . '</option></select>';
    }

    protected function getTestOptionText()
    {
        return __('--Select an option--');
    }
}
