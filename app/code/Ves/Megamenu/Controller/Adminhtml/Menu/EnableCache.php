<?php
/**
 * Venustheme
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the Venustheme.com license that is
 * available through the world-wide-web at this URL:
 * http://www.venustheme.com/license-agreement.html
 * 
 * DISCLAIMER
 * 
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 * 
 * @category   Venustheme
 * @package    Ves_Megamenu
 * @copyright  Copyright (c) 2017 Venustheme (http://www.venustheme.com/)
 * @license    http://www.venustheme.com/LICENSE-1.0.html
 */

namespace Ves\Megamenu\Controller\Adminhtml\Menu;
use Magento\Framework\App\Filesystem\DirectoryList;

class EnableCache extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $_resource;

    /**
     * @var \Magento\Config\Model\ResourceModel\Config
     */
    protected $_resourceConfig;

    /**
     * @param \Magento\Backend\App\Action\Context       $context  
     * @param \Magento\Framework\App\ResourceConnection $resource 
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Config\Model\ResourceModel\Config $resourceConfig
    ) {
        parent::__construct($context);
        $this->_resource       = $resource;
        $this->_resourceConfig = $resourceConfig;
    }

    /**
     * Save action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        try {
            $this->_resourceConfig->saveConfig(
                'vesmegamenu/general_settings/enable_cache',
                1,
                'default',
                1
                );
            $this->messageManager->addSuccess(__('Menu Cache were enabled.'));
        } catch (\Exception $e) {
            $this->messageManager->addException($e, __('Something went wrong while refresh menu cache.'));
        }
        return $resultRedirect->setPath('*/*/');
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Ves_Megamenu::menu_save');
    }
}