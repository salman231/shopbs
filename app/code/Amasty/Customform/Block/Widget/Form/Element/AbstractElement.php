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

class AbstractElement
{
    protected $options = [
        'title'         => '',
        'image_href'    => '',
    ];

    public function __construct()
    {
        $this->_construct();
    }

    public function _construct()
    {
        //override in parent classes
    }

    public function getHtml()
    {
        $html = '<div class="amelement-container">';
            $html .= '<div class="amelement-leftvisible">';
                $html .= '<div class="amelement-image">';
                    $html .= '<img href="' . $this->options['image_href'] . '" alt="' . $this->options['title'] . '">';
                $html .= '</div>';
                $html .= '<div class="amelement-title">';
                $html .= '<span>' . $this->options['title'] . '</span>';
                $html .= '</div>';
            $html .= '</div>';
        $html .= '</div>';

        return $html;
    }

    public function getElementData($type, $parentType)
    {
        $result = [
            'label' => $this->options['title'],
            'content' => $this->generateContent(),
            'attrs' => [
                'type'       => $type,
                'parentType' => $parentType,
                'className'  => 'amform-' . $type,
                'name'       => 'amform-' . $type,
                'element_id' => '',
                'format'     => $this->getInputFormat()
            ],

        ];

        return $result;
    }

    /**
     * @return string
     */
    public function generateContent()
    {
        return '';
    }

    /**
     * @return string
     */
    public function getInputFormat()
    {
        return '';
    }
}
