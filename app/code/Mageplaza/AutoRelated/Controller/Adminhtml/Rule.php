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
use Magento\Backend\Model\View\Result\ForwardFactory;
use Magento\Backend\Model\View\Result\Page;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\Filter\Date;
use Magento\Framework\View\Result\PageFactory;
use Mageplaza\AutoRelated\Helper\Data;
use Mageplaza\AutoRelated\Model\RuleFactory;
use Psr\Log\LoggerInterface;

/**
 * Class Rule
 * @package Mageplaza\AutoRelated\Controller\Adminhtml
 */
abstract class Rule extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Mageplaza_AutoRelated::rule';

    /**
     * @var ForwardFactory
     */
    protected $resultForwardFactory;

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var RuleFactory
     */
    protected $autoRelatedRuleFactory;

    /**
     * Core registry
     *
     * @var Registry
     */
    protected $coreRegistry = null;

    /**
     * @var Date
     */
    protected $_dateFilter;

    /**
     * @var Data
     */
    protected $helperData;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Rule constructor.
     *
     * @param Context $context
     * @param ForwardFactory $resultForwardFactory
     * @param PageFactory $resultPageFactory
     * @param RuleFactory $autoRelatedRuleFactory
     * @param Date $dateFilter
     * @param Registry $coreRegistry
     * @param Data $helperData
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        ForwardFactory $resultForwardFactory,
        PageFactory $resultPageFactory,
        RuleFactory $autoRelatedRuleFactory,
        Date $dateFilter,
        Registry $coreRegistry,
        Data $helperData,
        LoggerInterface $logger
    ) {
        $this->resultForwardFactory   = $resultForwardFactory;
        $this->resultPageFactory      = $resultPageFactory;
        $this->autoRelatedRuleFactory = $autoRelatedRuleFactory;
        $this->coreRegistry           = $coreRegistry;
        $this->_dateFilter            = $dateFilter;
        $this->helperData             = $helperData;
        $this->logger                 = $logger;

        parent::__construct($context);
    }

    /**
     * Init layout, menu and breadcrumb
     *
     * @return Page
     */
    protected function _initAction()
    {
        /** @var Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Mageplaza_AutoRelated::rule');
        $resultPage->addBreadcrumb(__('AutoRelated'), __('Manage Rules'));

        return $resultPage;
    }
}
