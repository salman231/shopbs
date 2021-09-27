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

class Htwo extends Text
{
    public function _construct()
    {
        parent::_construct();

        $this->options['title'] = __('H2');
    }

    public function generateContent()
    {
        return '<h2 class="title">' . $this->getExamplePhrase() . '</h2>';
    }
}
