<?php
/**
 * Transition.php
 *
 * @category  Webkul
 * @package   Webkul_Marketplace
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Marketplace\Model\Config\Source;
/**
 * Used in seller featured widget for getting slide transition value.
 */
class Transition implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter.
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'slide', 'label' => __('Slide Show')],
            ['value' => 'fade', 'label' => __('Fade Out')]
        ];
    }
}
