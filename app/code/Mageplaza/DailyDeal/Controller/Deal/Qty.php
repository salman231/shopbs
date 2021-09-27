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

namespace Mageplaza\DailyDeal\Controller\Deal;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\LayoutFactory;
use Mageplaza\DailyDeal\Block\Product\View\QtyItems as QtyStatus;
use Mageplaza\DailyDeal\Helper\Data as HelperData;

/**
 * Class Qty
 * @package Mageplaza\DailyDeal\Controller\Deal
 */
class Qty extends Action
{
    /**
     * @var LayoutFactory
     */
    protected $resultLayoutFactory;

    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var HelperData
     */
    protected $_helperData;

    /**
     * @var Qty
     */
    protected $_qty;

    /**
     * Qty constructor.
     *
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param LayoutFactory $resultLayoutFactory
     * @param HelperData $helperData
     * @param QtyStatus $qty
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        LayoutFactory $resultLayoutFactory,
        HelperData $helperData,
        QtyStatus $qty
    ) {
        $this->resultLayoutFactory = $resultLayoutFactory;
        $this->resultJsonFactory   = $resultJsonFactory;
        $this->_helperData         = $helperData;
        $this->_qty                = $qty;

        parent::__construct($context);
    }

    /**
     * @return ResponseInterface|Json|ResultInterface
     */
    public function execute()
    {
        $result     = $this->resultJsonFactory->create();
        $simpleId   = $this->getRequest()->getParam('id');
        $product_id = $this->getRequest()->getParam('deal_configurable_child_id') ?: $simpleId;
        $qtyRemain  = $this->_qty->getQtyRemain($product_id);
        $qtySold    = $this->_qty->getQtySold($product_id);
        $result->setData([
            'qtyRemain' => $qtyRemain,
            'qtySold'   => $qtySold
        ]);

        return $result;
    }
}
