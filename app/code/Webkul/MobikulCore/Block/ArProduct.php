<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MobikulCore
 * @author    Webkul <support@webkul.com>
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html ASL Licence
 * @link      https://store.webkul.com/license.html
 */

namespace Webkul\MobikulCore\Block;

class ArProduct extends \Magento\Framework\View\Element\Template
{
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Webkul\MobikulCore\Helper\Catalog $catalogHelper,
        \Magento\Framework\HTTP\Header $httpHeader,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->httpHeader = $httpHeader;
        $this->coreRegistry = $registry;
        $this->catalogHelper = $catalogHelper;
        parent::__construct($context, $data);
    }

    /**
     * Function to verify if the current product is an AR Product
     *
     * @return bool
     */
    public function isAr()
    {
        $product = $this->coreRegistry->registry('current_product');
        if ($product->getArType()) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Function to get the AR file of the current product
     *
     * @return string|bool
     */
    public function getArProductData()
    {
        $product = $this->coreRegistry->registry('current_product');
        if ($product->getArType()) {
            return $product->getArModelFileIos();
        } else {
            return false;
        }
    }
}
