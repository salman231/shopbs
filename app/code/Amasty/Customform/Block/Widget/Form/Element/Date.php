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

class Date extends AbstractElement
{
    /**
     * @var \Amasty\Customform\Helper\Data
     */
    private $helper;

    public function __construct(\Amasty\Customform\Helper\Data $helper)
    {
        parent::__construct();
        $this->helper = $helper;
    }

    public function _construct()
    {
        parent::_construct();
        $this->options['title'] = __('Date');
        $this->options['image_href'] = 'Amasty_Customform::images/date.png';
    }

    /**
     * @inheritdoc
     */
    public function generateContent()
    {
        return '<input class="form-control" type="date"/>';
    }

    /**
     * @inheritdoc
     */
    public function getInputFormat()
    {
        return $this->helper->getDateFormat();
    }
}
