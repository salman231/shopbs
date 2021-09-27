<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Mpreportsystem
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Mpreportsystem\Block\Account;

class Navigation extends \Magento\Framework\View\Element\Html\Link
{
    /**
     * @var \Webkul\Marketplace\Helper\Data
     */
    protected $_mpHelper;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Webkul\Mpreportsystem\Helper\Data $mpreportHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Webkul\Marketplace\Helper\Data $mpHelper,
        array $data = []
    ) {
        $this->_mpHelper = $mpHelper;
        parent::__construct($context, $data);
    }
    /**
     * Get current url
     *
     * @return string
     */
    public function getCurrentUrl()
    {
        return $this->_urlBuilder->getCurrentUrl();
    }

    /**
     * Return the Customer seller status.
     *
     * @return bool|0|1
     */
    public function isSeller()
    {
        return $this->_mpHelper->isSeller();
    }

    /**
     * Return the Seller Group Module status.
     *
     * @return bool|0|1
     */
    public function isSellerGroupModuleInstalled()
    {
        return $this->_mpHelper->isSellerGroupModuleInstalled();
    }

    /**
     * Return the whether the action is allowed or not
     *
     * @return bool|0|1
     */
    public function isAllowedAction($actionName = '')
    {
        return $this->_mpHelper->isAllowedAction($actionName);
    }
}
