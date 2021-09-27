<?php
/**
 * Venustheme
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the Venustheme.com license that is
 * available through the world-wide-web at this URL:
 * http://www.venustheme.com/license-agreement.html
 * 
 * DISCLAIMER
 * 
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 * 
 * @category   Venustheme
 * @package    Ves_Megamenu
 * @copyright  Copyright (c) 2017 Venustheme (http://www.venustheme.com/)
 * @license    http://www.venustheme.com/LICENSE-1.0.html
 */

namespace Ves\Megamenu\Model\Config\Source;

class BorderStyle implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        $options = [];
        $options[] = [
                'label' => __('Theme defaults'),
                'value' => '',
            ];
        $options[] = [
                'label' => __('Solid'),
                'value' => 'solid',
        ];
        $options[] = [
                'label' => __('Dotted'),
                'value' => 'dotted',
        ];
        $options[] = [
                'label' => __('Dashed'),
                'value' => 'dashed',
        ];
        $options[] = [
                'label' => __('None'),
                'value' => 'none',
        ];
        $options[] = [
                'label' => __('Hidden'),
                'value' => 'hidden',
            ];
        $options[] = [
                'label' => __('Double'),
                'value' => 'double',
        ];
        $options[] = [
                'label' => __('Groove'),
                'value' => 'groove',
        ];
        $options[] = [
                'label' => __('Ridge'),
                'value' => 'ridge',
        ];
        $options[] = [
                'label' => __('Inset'),
                'value' => 'inset',
        ];
        $options[] = [
                'label' => __('Outset'),
                'value' => 'outset',
        ];
        $options[] = [
                'label' => __('Initial'),
                'value' => 'initial',
        ];
        $options[] = [
                'label' => __('Inherit'),
                'value' => 'inherit',
        ];
        return $options;
    }
}
