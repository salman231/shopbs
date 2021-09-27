<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Methods
 */

/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Amasty\Methods\Controller\Adminhtml\Shipping;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Index extends \Magento\Backend\App\Action
{
    protected $resultPageFactory;
    protected $storeManager;

    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->storeManager = $storeManager;
        $this->coreRegistry = $coreRegistry;
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Amasty_Methods::methods_shipping');
    }
    /**
     * Index action
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $websiteId = $this->getRequest()->getParam('website_id');

        if ($websiteId === null && $this->storeManager->getWebsite()->getId()){
            $this->_redirect('*/*/*', [
                'website_id' => $this->storeManager->getWebsite()->getId()
            ]);
        } else {

            $structure = $this->_objectManager->create('\Amasty\Methods\Model\Structure\Shipping')
                ->load($websiteId);

            $this->coreRegistry->register(
                \Amasty\Methods\Controller\Adminhtml\RegistryConstants::CURRENT_AMASTY_METHODS_SHIPPING,
                $structure
            );

            /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
            $resultPage = $this->resultPageFactory->create();
            $resultPage->setActiveMenu('Amasty_Methods::methods_shipping');
            $resultPage->addBreadcrumb(__('System'), __('System'));
            $resultPage->addBreadcrumb(__('Manage Shipping Methods Visibility'), __('Manage Shipping Methods Visibility'));
            $resultPage->getConfig()->getTitle()->prepend(__('Shipping Methods Visibility'));

            return $resultPage;
        }
    }
}
