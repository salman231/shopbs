<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_AutoRelated
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\AutoRelated\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Framework\Registry;

/**
 * Class LocationOptionsProvider
 * @package Mageplaza\AutoRelated\Model\Config\Source
 */
class LocationOptionsProvider implements OptionSourceInterface
{
    /**
     * @var Registry
     */
    protected $coreRegistry;

    /**
     * @param Registry $coreRegistry
     */
    public function __construct(Registry $coreRegistry)
    {
        $this->coreRegistry = $coreRegistry;
    }

    /**
     * Return array of options as value-label pairs
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [];

        $type = $this->coreRegistry->registry('autorelated_type');
        if ($type === Type::TYPE_PAGE_PRODUCT) {
            $options = [
                ['value' => 'replace-related', 'label' => __('Replace Related Products ')],
                ['value' => 'replace-upsell', 'label' => __('Replace Upsell Products')],
                ['value' => 'before-related', 'label' => __('Before native Related Products')],
                ['value' => 'after-related', 'label' => __('After native Related Products')],
                ['value' => 'before-upsell', 'label' => __('Before Upsell Products')],
                ['value' => 'after-upsell', 'label' => __('After Upsell Products')]
            ];
        } elseif ($type === Type::TYPE_PAGE_SHOPPING) {
            $options = [
                ['value' => 'replace-cross', 'label' => __('Replace Cross-sell Products')],
                ['value' => 'before-cross', 'label' => __('Before Cross-sell Products')],
                ['value' => 'after-cross', 'label' => __('After Cross-sell Products')]
            ];
        } elseif ($type === Type::TYPE_PAGE_CATEGORY) {
            $options = [
                ['value' => 'before-sidebar', 'label' => __('Sidebar Top')],
                ['value' => 'after-sidebar', 'label' => __('Sidebar Bottom')]
            ];
        }

        return array_merge([
            ['value' => 'before-content', 'label' => __('Above Content')],
            ['value' => 'after-content', 'label' => __('Below Content')],
            ['value' => 'left-popup-content', 'label' => __('Floating Left Bar')],
            ['value' => 'right-popup-content', 'label' => __('Floating Right Bar')],
            ['value' => 'custom', 'label' => __('Manually')]
        ], $options);
    }
}
