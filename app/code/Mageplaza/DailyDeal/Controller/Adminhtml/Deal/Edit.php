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
 * @package     Mageplaza_DailyDeal
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\DailyDeal\Controller\Adminhtml\Deal;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use Mageplaza\DailyDeal\Controller\Adminhtml\Deal;
use Mageplaza\DailyDeal\Model\DealFactory;

/**
 * Class Edit
 * @package Mageplaza\DailyDeal\Controller\Adminhtml\Deal
 */
class Edit extends Deal
{
    /**
     * Page factory
     *
     * @var PageFactory
     */
    protected $_resultPageFactory;

    /**
     * Result JSON factory
     *
     * @var JsonFactory
     */
    protected $_resultJsonFactory;

    /**
     * Edit constructor.
     *
     * @param Context $context
     * @param DealFactory $dealFactory
     * @param Registry $coreRegistry
     * @param PageFactory $resultPageFactory
     * @param JsonFactory $resultJsonFactory
     */
    public function __construct(
        Context $context,
        DealFactory $dealFactory,
        Registry $coreRegistry,
        PageFactory $resultPageFactory,
        JsonFactory $resultJsonFactory
    ) {
        $this->_resultPageFactory = $resultPageFactory;
        $this->_resultJsonFactory = $resultJsonFactory;

        parent::__construct($context, $dealFactory, $coreRegistry);
    }

    /**
     * @return ResponseInterface|Redirect|ResultInterface|Page
     */
    public function execute()
    {
        $deal = $this->_initDeal();
        if (!$deal->getId() && $this->getRequest()->getParam('id')) {
            $this->messageManager->addErrorMessage(__('This Deal no longer exists.'));
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('*/*/', [
                'id'       => $deal->getId(),
                '_current' => true
            ]);

            return $resultRedirect;
        }

        $resultPage = $this->_resultPageFactory->create();
        $resultPage->setActiveMenu('Mageplaza_DailyDeal::deal');
        $resultPage->getConfig()->getTitle()
            ->set(__('Daily Deal'))
            ->prepend($deal->getId() ? $deal->getProductName() : __('Create Deal'));

        return $resultPage;
    }
}
