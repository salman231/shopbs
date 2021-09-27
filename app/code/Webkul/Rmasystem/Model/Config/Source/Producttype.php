<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Rmasystem
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Rmasystem\Model\Config\Source;

/**
 * Used in creating options for getting product type value.
 */
class Producttype
{
    /**
     * @var \Magento\Catalog\Model\ProductTypes\ConfigInterface
     */
    protected $_config;

    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $manager;
    /**
     * Construct.
     *
     * @param \Magento\Catalog\Model\ProductTypes\ConfigInterface $config
     */
    public function __construct(
        \Magento\Framework\Module\Manager $manager,
        \Magento\Catalog\Model\ProductTypes\ConfigInterface $config
    ) {
        $this->manager = $manager;
        $this->_config = $config;
    }
    /**
     * Options getter.
     *
     * @return array
     */
    public function toOptionArray()
    {
        $data = [
            ['value' => 'simple', 'label' => __('Simple')],
            ['value' => 'downloadable', 'label' => __('Downloadable')],
            ['value' => 'virtual', 'label' => __('Virtual')],
            ['value' => 'configurable', 'label' => __('Configurable')],
            ['value' => 'bundle', 'label' => __('Bundle Product')],
            ['value' => 'grouped', 'label' => __('Grouped Product')]
        ];

        if ($this->manager->isEnabled('Webkul_BookingSystem')) {
            $data[] = ['value' => 'booking', 'label' => __('Booking Product')];
        }
        return $data;
    }
}
