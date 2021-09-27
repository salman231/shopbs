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

namespace Mageplaza\AutoRelated\Block\Product;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Mageplaza\AutoRelated\Helper\Rule;
use Mageplaza\AutoRelated\Model\Config\Source\DisplayMode;

/**
 * Class AutoRelated
 * @package Mageplaza\AutoRelated\Block\Product\ProductList
 */
class Ajax extends Template
{
    /**
     * Path to template file.
     *
     * @var string
     */
    protected $_template = 'Mageplaza_AutoRelated::product/ajax.phtml';

    /**
     * @var Data
     */
    protected $helper;

    /**
     * Ajax constructor.
     *
     * @param Context $context
     * @param Rule $helper
     * @param array $data
     */
    public function __construct(
        Context $context,
        Rule $helper,
        array $data = []
    ) {
        $this->helper = $helper;

        parent::__construct($context, $data);
    }

    /**
     * Get Data send ajax
     *
     * @return mixed
     */
    public function getAjaxData()
    {
        return Rule::jsonEncode([
            'type'      => $this->helper->getData('type'),
            'entity_id' => $this->helper->getData('entity_id'),
            'url'       => $this->getUrl('autorelated/ajax/load'),
        ]);
    }

    /**
     * @return bool
     */
    public function isAjaxLoad()
    {
        if (!$this->helper->isEnableArpBlock()) {
            return false;
        }

        $ajaxRules = $this->helper->getActiveRulesByMode(DisplayMode::TYPE_AJAX);

        return !empty($ajaxRules);
    }
}
