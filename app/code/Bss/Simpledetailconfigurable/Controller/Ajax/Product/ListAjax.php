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
 * @copyright  Copyright (c) 2017-2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\Simpledetailconfigurable\Controller\Ajax\Product;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\Layout;
use Bss\Simpledetailconfigurable\Controller\Ajax\Product as ProductController;
use Magento\Framework\Controller\ResultFactory;

class ListAjax extends ProductController
{
    /**
     * Show list of product's reviews
     *
     * @return ResponseInterface|ResultInterface|Layout
     */
    public function execute()
    {
        if (!$this->initProduct()) {
            /** @var \Magento\Framework\Controller\Result\Forward $resultForward */
            $resultForward = $this->resultFactory->create(ResultFactory::TYPE_FORWARD);
            return $resultForward->forward('noroute');
        }
        return $this->resultFactory->create(ResultFactory::TYPE_LAYOUT);
    }
}
