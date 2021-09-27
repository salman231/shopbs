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

class File extends AbstractElement
{
    public function _construct()
    {
        parent::_construct();
        $this->options['title'] = __('File');
        $this->options['image_href'] = 'Amasty_Customform::images/upload.png';
    }

    public function generateContent()
    {
        return '<input class="form-control" type="file"/>';
    }
}
