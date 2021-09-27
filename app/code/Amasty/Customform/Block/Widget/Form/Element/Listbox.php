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

class Listbox extends Dropdown
{
    public function _construct()
    {
        parent::_construct();
        $this->options['title'] = __('ListBox');
        $this->options['image_href'] = 'Amasty_Customform::images/listbox.png';
    }

    public function generateContent()
    {
        return '<select class="select multiselect admin__control-multiselect" multiple="multiple"><option value="">'
            . $this->getTestOptionText()
            . '</option></select>';
    }
}
