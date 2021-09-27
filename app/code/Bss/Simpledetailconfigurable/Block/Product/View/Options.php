<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_Simpledetailconfigurable
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\Simpledetailconfigurable\Block\Product\View;

use Magento\Catalog\Model\Product;

class Options extends \Magento\Framework\View\Element\Template
{
    /**
     * @var Product
     */
    protected $_product;

    /**
     * Product option
     *
     * @var \Magento\Catalog\Model\Product\Option
     */
    protected $_option;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_registry = null;

    /**
     * Catalog product
     *
     * @var Product
     */
    protected $_catalogProduct;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $_jsonEncoder;

    /**
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    protected $pricingHelper;

    /**
     * @var \Magento\Catalog\Helper\Data
     */
    protected $_catalogData;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    protected $serializer;

    /**
     * @var \Magento\Framework\Stdlib\ArrayUtils
     */
    protected $arrayUtils;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Pricing\Helper\Data $pricingHelper,
        \Magento\Catalog\Helper\Data $catalogData,
        \Magento\Catalog\Model\Product\Option $option,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Stdlib\ArrayUtils $arrayUtils,
        \Magento\Framework\Serialize\Serializer\Json $serializer,
        array $data = []
    ) {
        $this->pricingHelper = $pricingHelper;
        $this->_catalogData = $catalogData;
        $this->_registry = $registry;
        $this->_option = $option;
        $this->arrayUtils = $arrayUtils;
        $this->serializer = $serializer;
        parent::__construct($context, $data);
    }

    /**
     * Retrieve product object
     *
     * @return Product
     * @throws \LogicExceptions
     */
    public function getProduct()
    {
        if (!$this->_product) {
            if ($this->_registry->registry('current_product')) {
                $this->_product = $this->_registry->registry('current_product');
            } else {
                throw new \LogicException('Product is not defined');
            }
        }
        return $this->_product;
    }

    /**
     * @return bool|false|string
     */
    public function getJsonConfig()
    {
        $priceValue['ajaxLoadUrl'] = $this->getAjaxLoadUrl();
        return $this->serializer->serialize($priceValue);
    }

    /**
     * get ajax url
     *
     * @return string
     */
    public function getAjaxLoadUrl()
    {
        return $this->_urlBuilder->getUrl('bss_sdcp/ajax/option');
    }
}
