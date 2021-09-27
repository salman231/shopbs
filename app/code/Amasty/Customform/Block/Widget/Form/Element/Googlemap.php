<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Customform
 */


namespace Amasty\Customform\Block\Widget\Form\Element;

class Googlemap extends AbstractElement
{
    public function _construct()
    {
        parent::_construct();
        $this->options['title'] = __('Google Map');
    }

    public function generateContent()
    {
        return '<div></div>';
    }
}
