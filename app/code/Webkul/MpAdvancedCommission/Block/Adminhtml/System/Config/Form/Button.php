<?php
/**
 * @category   Webkul
 * @package    Webkul_MpAdvancedCommission
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\MpAdvancedCommission\Block\Adminhtml\System\Config\Form;

use Magento\Framework\Data\Form\Element\AbstractElement;

use Magento\Catalog\Model\Category;

/**
 * Order view tabs
 */
class Button extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * @var \Magento\Catalog\Model\Category
     */
    protected $_category;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;

    /**
     * @param \Magento\Framework\View\Element\Context $context
     * @param Category $category
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        Category $category,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        array $data = []
    ) {
        $this->_category = $category;
        $this->jsonHelper = $jsonHelper;
        parent::__construct($context, $data);
    }

    /**
     * @return $this
     */
    public function _construct()
    {
        $this->setTemplate('system/config/button.phtml');
        return $this;
    }

    /**
     * Retrieve element HTML markup
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        return $this->_toHtml();
    }
 
    /**
     * Return ajax url for button
     *
     * @return string
     */
    public function getAjaxCheckUrl()
    {
        return $this->getUrl('mpadvancedcommission/ajax/check');
    }

    /**
     * Create button and return its html
     *
     * @return string
     */
    public function getButtonHtml()
    {
        return $this->getLayout()->createBlock(
            \Magento\Backend\Block\Widget\Button::class
        )->setData(
            [
                'label' => __('Set Per Category Commission'),
                'type' => 'button',
                'id' => 'mpadvancedcommission_options_category_commission'
            ]
        )->toHtml();
    }

    /**
     * return category
     *
     * @return void
     */
    public function getCategory()
    {
        return $this->_category;
    }

    /**
     * encodes data in json format
     *
     * @param array $data
     * @return json array
     */
    public function jsonEncode($data)
    {
        return $this->jsonHelper->jsonEncode($data);
    }
}
