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

namespace Mageplaza\AutoRelated\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Mageplaza\AutoRelated\Helper\Data;

/**
 * Class Related
 * @package Mageplaza\AutoRelated\Ui\DataProvider\Product\Form\Modifier
 */
class Related extends AbstractModifier
{
    /**
     * @var Data
     */
    protected $helperData;

    /**
     * Related constructor.
     *
     * @param Data $helperData
     */
    public function __construct(Data $helperData)
    {
        $this->helperData = $helperData;
    }

    /**
     * @param array $data
     *
     * @return array
     */
    public function modifyData(array $data)
    {
        return $data;
    }

    /**
     * @param array $meta
     *
     * @return array
     */
    public function modifyMeta(array $meta)
    {
        if ($this->helperData->isEnabled()) {
            $this->setFieldRelatedProduct($meta, 'mp_disable_auto_related', 'data.product.mp_disable_auto_related');
        } else {
            $this->disableFields($meta);
        }

        return $meta;
    }

    /**
     * Set field template in Related Products section
     *
     * @param $meta
     * @param $field
     * @param $data
     *
     * @return mixed
     */
    public function setFieldRelatedProduct(&$meta, $field, $data)
    {
        if (isset($meta['related']['children']['container_' . $field])) {
            $meta['related']['children']['container_' . $field]['children'][$field]['arguments']['data']['config']['dataScope'] = $data;
        }

        return $meta;
    }

    /**
     * Disable fields
     *
     * @param $meta
     */
    public function disableFields(&$meta)
    {
        if (isset($meta['related']['children']['container_mp_disable_auto_related'])) {
            $meta['related']['children']['container_mp_disable_auto_related']['children']['mp_disable_auto_related']['arguments']['data']['config']['visible'] = 0;
        }
    }
}
