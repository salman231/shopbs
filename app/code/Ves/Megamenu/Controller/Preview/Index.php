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

namespace Ves\Megamenu\Controller\Preview;

use Magento\Backend\App\Action;

class Index extends \Magento\Framework\App\Action\Action
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $_resource;

    /**
     * @var \Ves\Megamenu\Helper\Editor
     */
    protected $editor;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @param \Magento\Framework\App\Action\Context      $context           
     * @param \Magento\Framework\App\ResourceConnection  $resource          
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager      
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory 
     * @param \Ves\Megamenu\Helper\Editor                $editor            
     * @param \Magento\Framework\Registry                $registry          
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Ves\Megamenu\Helper\Editor $editor,
        \Magento\Framework\Registry $registry
    ) {
    	parent::__construct($context);
        $this->resultPageFactory  = $resultPageFactory;
        $this->_coreRegistry      = $registry;
        $this->_resource          = $resource;
        $this->editor             = $editor;
        $this->_storeManager      = $storeManager;
    }

    /**
     * Default customer account page
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $page   = $this->resultPageFactory->create();
        $params = $this->getRequest()->getParams();
        $model  = $this->_objectManager->create('Ves\Megamenu\Model\Menu');

        if (isset($params['menu_id'])) {
            $model->load($params['menu_id']);
            if ($model->getParams()) {
                $params = unserialize($model->getParams());
                $model->setStructure($params['structure']);
                $model->setDesign($params['design']);
                $model->setIsPreview(true);
            }
            $this->_coreRegistry->register('current_menu', $model);
        }
        return $page;
    }
}