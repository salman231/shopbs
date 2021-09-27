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
namespace Webkul\Rmasystem\Block\Guest;

use Magento\Framework\Session\SessionManager;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Guest Login block
 */
class Login extends \Magento\Framework\View\Element\Template
{
 /**
  * @var  \Magento\Framework\Data\Form\FormKey
  */
    protected $_formKey;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\View\Element\FormKey $formKey
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\View\Element\FormKey $formKey,
        array $data = []
    ) {
        $this->_formKey = $formKey;
        parent::__construct($context, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->pageConfig->getTitle()->set(__('Guest Login for RMA'));
    }

    /**
     * Get form key
     *
     * @return string
     */
    public function getFormKey()
    {
        return $this->_formKey->getFormKey();
    }
}
