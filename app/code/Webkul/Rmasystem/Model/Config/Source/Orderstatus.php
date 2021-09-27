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

class Orderstatus implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options[] = ['label'=>__('Select Status Type'), 'value' => ''];
        $options[] = ['label'=>__('Complete'), 'value' => 'complete'];
        $options[] =['label'=>__('All Status'), 'value' => 'all'];
        return $options;
    }
}
