<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_Simpledetailconfigurable
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\Simpledetailconfigurable\Controller\Ajax;

use Magento\Framework\App\Action\Context;

/**
 * Class Detail
 *
 * @package Bss\Simpledetailconfigurable\Controller\Ajax
 * @SuppressWarnings(PHPMD.AllPurposeAction)
 */
class Detail extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Bss\Simpledetailconfigurable\Helper\ProductData
     */
    private $productData;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    private $resultPageFactory;

    /**
     * Detail constructor.
     *
     * @param Context $context
     * @param \Bss\Simpledetailconfigurable\Helper\ProductData $productData
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        \Bss\Simpledetailconfigurable\Helper\ProductData $productData,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        $this->productData = $productData;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->resultPageFactory  = $resultPageFactory;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Json|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $this->resultPageFactory->create();
        $resultJson = $this->resultJsonFactory->create();
        if ($this->getRequest()->isAjax()) {
            $childId = $this->getRequest()->getParam('product_id');
            $result = $this->productData->getChildDetail($childId);
            return $resultJson->setData($result);
        } else {
            return $resultJson->setData(null);
        }
    }
}
