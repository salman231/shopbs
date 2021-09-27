<?php
/**
 * @category   Webkul
 * @package    Webkul_MpAdvancedCommission
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\MpAdvancedCommission\Block\Adminhtml\Customer\Edit;

use Magento\Customer\Controller\RegistryConstants;
use Magento\Ui\Component\Layout\Tabs\TabInterface;
use Magento\Backend\Block\Widget\Form;
use Magento\Backend\Block\Widget\Form\Generic;

use Magento\Catalog\Model\Category;
use Magento\Customer\Model\Customer;

/**
 * Customer Seller form block.
 */
class Tabs extends Generic implements TabInterface
{
    /**
     * @var Category
     */
    protected $_category;

    /**
     * @var Customer
     */
    protected $_customer;

    /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * Core registry.
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * Core registry.
     *
     * @var \Webkul\MpAdvancedCommission\Helper\Data
     */
    protected $_helper;

     /**
      * @param \Magento\Backend\Block\Template\Context $context
      * @param \Magento\Framework\Registry $registry
      * @param \Magento\Framework\Data\FormFactory $formFactory
      * @param \Magento\Framework\ObjectManagerInterface $objectManager
      * @param Category $category
      * @param Customer $customer
      * @param \Webkul\Marketplace\Block\Adminhtml\Customer\Edit $customerEdit
      * @param \Webkul\MpAdvancedCommission\Helper\Data $helper
      * @param array $data
      */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        Category $category,
        Customer $customer,
        \Webkul\Marketplace\Block\Adminhtml\Customer\Edit $customerEdit,
        \Webkul\MpAdvancedCommission\Helper\Data $helper,
        array $data = []
    ) {
        $this->_customer = $customer;
        $this->_coreRegistry = $registry;
        $this->_objectManager = $objectManager;
        $this->_category = $category;
        $this->_customerEdit=$customerEdit;
        $this->_helper = $helper;
        $this->setTemplate('Webkul_MpAdvancedCommission::action.phtml');
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * @return string|null
     */
    public function getCustomer()
    {
        return $this->_customer->load($this->getCustomerId());
    }

    /**
     * @return string|null
     */
    public function getCustomerId()
    {
        return $this->_coreRegistry->registry(
            RegistryConstants::CURRENT_CUSTOMER_ID
        );
    }

    /**
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('Category Commission');
    }

    /**
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Category Commission');
    }

    /**
     * @return bool
     */
    public function canShowTab()
    {
        $coll = $this->_customerEdit->getMarketplaceUserCollection();
        $isSeller = false;
        foreach ($coll as $row) {
            $isSeller = $row->getIsSeller();
        }
        if ($this->getCustomerId() && $isSeller) {
            return true;
        }

        return false;
    }

    /**
     * @return bool
     */
    public function isHidden()
    {
        $coll = $this->_customerEdit->getMarketplaceUserCollection();
        $isSeller = false;
        foreach ($coll as $row) {
            $isSeller = $row->getIsSeller();
        }
        if ($this->getCustomerId() && $isSeller) {
            return false;
        }

        return true;
    }

    /**
     * Tab class getter.
     *
     * @return string
     */
    public function getTabClass()
    {
        return '';
    }

    /**
     * Return URL link to Tab content.
     *
     * @return string
     */
    public function getTabUrl()
    {
        return '';
    }

    /**
     * Tab should be loaded trough Ajax call.
     *
     * @return bool
     */
    public function isAjaxLoaded()
    {
        return false;
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
     * get Commission type applied
     *
     * @return void
     */
    public function getCommissionType()
    {
        return $this->_helper->getCommissionType();
    }
}
