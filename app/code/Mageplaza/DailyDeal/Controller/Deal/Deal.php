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
use Mageplaza\DailyDeal\Block\Product\View\Label;
use Mageplaza\DailyDeal\Block\Product\View\QtyItems;

/**
 * Class Deal
 * @package Mageplaza\DailyDeal\Controller\Deal
 */
class Deal extends Action
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
     * Deal constructor.
     *
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param LayoutFactory $resultLayoutFactory
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        LayoutFactory $resultLayoutFactory
    ) {
        $this->resultLayoutFactory = $resultLayoutFactory;
        $this->resultJsonFactory   = $resultJsonFactory;

        parent::__construct($context);
    }

    /**
     * @return ResponseInterface|Json|ResultInterface
     */
    public function execute()
    {
        $result       = $this->resultJsonFactory->create();
        $resultLayout = $this->resultLayoutFactory->create();
        $dealHtml     = $resultLayout->getLayout()
            ->createBlock(\Mageplaza\DailyDeal\Block\Product\View\Countdown::class)
            ->setTemplate('Mageplaza_DailyDeal::product/view/countdown.phtml')
            ->toHtml();
        $dealHtml     .= $resultLayout->getLayout()
            ->createBlock(QtyItems::class)
            ->setTemplate('Mageplaza_DailyDeal::product/view/qty.phtml')
            ->toHtml();
        $dealHtml     .= $resultLayout->getLayout()
            ->createBlock(Label::class)
            ->setTemplate('Mageplaza_DailyDeal::product/view/label.phtml')
            ->toHtml();
        $result->setData(['success' => $dealHtml]);

        return $result;
    }
}
