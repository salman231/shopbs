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

namespace Mageplaza\AutoRelated\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\Filter\Date;

/**
 * Class ConditionAction
 * @package Mageplaza\AutoRelated\Controller\Adminhtml
 */
abstract class ConditionAction extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Mageplaza_AutoRelated::rule';

    /**
     * Dirty rules notice message
     *
     *
     * @var string
     */
    protected $_dirtyRulesNoticeMessage;

    /**
     * Core registry
     *
     * @var Registry
     */
    protected $_coreRegistry = null;

    /**
     * Date filter instance
     *
     * @var Date
     */
    protected $_dateFilter;

    /**
     * Constructor
     *
     * @param Context $context
     * @param Registry $coreRegistry
     * @param Date $dateFilter
     */
    public function __construct(Context $context, Registry $coreRegistry, Date $dateFilter)
    {
        $this->_coreRegistry = $coreRegistry;
        $this->_dateFilter   = $dateFilter;

        parent::__construct($context);
    }

    /**
     * Init action
     *
     * @return $this
     */
    protected function _initAction()
    {
        $this->_view->loadLayout();
        $this->_setActiveMenu('Mageplaza_AutoRelated::rule')
            ->_addBreadcrumb(__('Promotions'), __('Promotions'));

        return $this;
    }

    /**
     * Set dirty rules notice message
     *
     * @param string $dirtyRulesNoticeMessage
     *
     * @return void
     * @codeCoverageIgnore
     */
    public function setDirtyRulesNoticeMessage($dirtyRulesNoticeMessage)
    {
        $this->_dirtyRulesNoticeMessage = $dirtyRulesNoticeMessage;
    }

    /**
     * Get dirty rules notice message
     *
     * @return string
     */
    public function getDirtyRulesNoticeMessage()
    {
        $type = $this->_coreRegistry->registry('autorelated_type');
        if ($type == 'cart' || $type == 'osc') {
            return false;
        }
        $defaultMessage = __(
            'We found updated rules that are not applied. Please click "Apply Rules" to update your catalog.'
        );

        return $this->_dirtyRulesNoticeMessage ? $this->_dirtyRulesNoticeMessage : $defaultMessage;
    }
}
