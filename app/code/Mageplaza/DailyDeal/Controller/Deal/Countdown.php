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

use Exception;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\View\Result\LayoutFactory;
use Mageplaza\DailyDeal\Block\Product\View\Countdown as TimeCountdown;
use Mageplaza\DailyDeal\Helper\Data as HelperData;

/**
 * Class Countdown
 * @package Mageplaza\DailyDeal\Controller\Deal
 */
class Countdown extends Action
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
     * @var DateTime
     */
    protected $_date;

    /**
     * @var TimeCountdown
     */
    protected $_timeCountdown;

    /**
     * Countdown constructor.
     *
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param LayoutFactory $resultLayoutFactory
     * @param HelperData $helperData
     * @param DateTime $date
     * @param TimeCountdown $timeCountdown
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        LayoutFactory $resultLayoutFactory,
        HelperData $helperData,
        DateTime $date,
        TimeCountdown $timeCountdown
    ) {
        $this->resultLayoutFactory = $resultLayoutFactory;
        $this->resultJsonFactory   = $resultJsonFactory;
        $this->_helperData         = $helperData;
        $this->_date               = $date;
        $this->_timeCountdown      = $timeCountdown;

        parent::__construct($context);
    }

    /**
     * @return ResponseInterface|Json|ResultInterface
     * @throws Exception
     */
    public function execute()
    {
        $result        = $this->resultJsonFactory->create();
        $simpleId      = $this->getRequest()->getParam('id');
        $product_id    = $this->getRequest()->getParam('deal_configurable_child_id') ?: $simpleId;
        $timeCountdown = $this->_timeCountdown->getTimeCountdown($product_id);

        $result->setData([
            'timeCountDown' => $timeCountdown
        ]);

        return $result;
    }
}
