<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Customform
 */


namespace Amasty\Customform\Block\Widget\Form\Element;

class Rating extends AbstractElement
{
    const STAR_COUNT = 5;

    public function _construct()
    {
        parent::_construct();

        $this->options['title'] = __('Rating');
    }

    public function generateContent()
    {
        $html = '<div class="amform-rating-container radio-group">';
        for ($counter = 1; $counter <= self::STAR_COUNT; $counter++) {
            $html .= $this->generateOneStar($counter);
        }
        
        $html .= '</div>';
        
        return $html;
    }

    /**
     * @param $counter
     * @return string
     */
    private function generateOneStar($counter)
    {
        return sprintf(
            '<input type="radio" name="ratings-example[]" id="amform-rating-%1$s" value="1" class="amform-rating">
             <label class="amform-versiontwo-label" for="amform-rating-%1$s" id="amform-rating-%1$s-label"></label>',
            $counter
        );
    }

    public function getLabelClassName()
    {
        return '';
    }
}
