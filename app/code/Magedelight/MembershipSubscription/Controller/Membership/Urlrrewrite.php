<?php

/**
* Magedelight
* Copyright (C) 2017 Magedelight <info@magedelight.com>
*
* @category Magedelight
* @package Magedelight_MembershipSubscription
* @copyright Copyright (c) 2017 Mage Delight (http://www.magedelight.com/)
* @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
* @author Magedelight <info@magedelight.com>
 * */

namespace Magedelight\MembershipSubscription\Controller\Membership;
	

class Urlrrewrite extends \Magento\Framework\App\Action\Action
{
	//protected $_urlRewrite;
	protected $_productFactory;
	public function __construct(
		\Magento\Framework\App\Action\Context $context,
		\Magento\Framework\View\Result\PageFactory $pageFactory,
    	\Magento\Catalog\Model\ProductFactory $productFactory
	){
		//$this->_urlRewrite = $urlRewrite;
		$this->_productFactory = $productFactory;
		parent::__construct($context);
	}
    public function execute()
    {
        $productId=4881;
        //$product = $this->_productFactory->load($productId);
        //print_r($product->getOptions());
        $_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $product = $_objectManager->get('\Magento\Catalog\Model\Product')->load($productId);
        $customOptions = $_objectManager->get('Magento\Catalog\Model\Product\Option')->getProductOptionCollection($product);
        //var_dump($customOptions);
    	exit;
    }
}
