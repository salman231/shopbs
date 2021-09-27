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

class Address extends AbstractElement
{
    public function _construct()
    {
        parent::_construct();

        $this->options['title'] = __('Address');
    }

    public function generateContent()
    {
        return '<h3 class="title">' . __('Address, City, State / Province / Region, Zipcode, Country') . '</h3>';
    }

    /**
     * @param $type
     * @param $parentType
     * @return array
     */
    public function getElementData($type, $parentType)
    {
        $result = parent::getElementData($type, $parentType);
        $result['childs'] = $this->getChildElements();

        return $result;
    }

    /**
     * @return array
     */
    private function getChildElements()
    {
        return [
            [
                'type' => 'textinput',
                'data' =>
                    [
                        'label' => __('Address'),
                    ]
            ],
            [
                'type' => 'textinput',
                'data' =>
                    [
                        'label' => __('City'),
                        'layout' => 'two'
                    ]
            ],
            [
                'type' => 'textinput',
                'data' =>
                [
                    'label' => __('State / Province / Region'),
                    'layout' => 'two'
                ]
            ],
            [
                'type' => 'textinput',
                'data' =>
                    [
                        'label' => __('Zipcode'),
                        'layout' => 'two'
                    ]
            ],
            [
                'type' => 'country',
                'data' =>
                    [
                        'label' => __('Country'),
                        'layout' => 'two'
                    ]
            ],
        ];
    }
}
