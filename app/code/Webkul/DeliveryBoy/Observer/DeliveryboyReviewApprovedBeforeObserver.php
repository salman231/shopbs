<?php
/**
 * Webkul Software.
 *
 * PHP version 7.0+
 *
 * @category  Webkul
 * @package   Webkul_DeliveryBoy
 * @author    Webkul <support@webkul.com>
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html ASL Licence
 * @link      https://store.webkul.com/license.html
 */
namespace Webkul\DeliveryBoy\Observer;

use Webkul\DeliveryBoy\Helper\Operation;
use Webkul\DeliveryBoy\Helper\Data;
use Psr\Log\LoggerInterface;
use Magento\Framework\DataObject;
use Webkul\DeliveryBoy\Helper\ModuleGlobalConstants;

class DeliveryboyReviewApprovedBeforeObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var Data
     */
    private $deliveryboyHelper;

    /**
     * @var Operation
     */
    private $operationHelper;
    
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param Data $deliveryboyHelper
     * @param Operation $operationHelper
     * @param LoggerInterface $logger
     */
    public function __construct(
        Data $deliveryboyHelper,
        Operation $operationHelper,
        LoggerInterface $logger
    ) {
        $this->deliveryboyHelper = $deliveryboyHelper;
        $this->logger = $logger;
        $this->operationHelper = $operationHelper;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try {
            $storeId = (int)$observer->getStoreId();
    
            $templateVars = [
                'deliveryboy' => $observer->getDeliveryboy(),
                'customerName' => $observer->getCustomerName(),
                'review' => $observer->getReview(),
                'ratingMaxLimit' => $observer->getRatingMaxLimit(),
                'ratingManagerName' => $observer->getRatingManagerName(),
            ];
            $senderInfo = $observer->getSenderInfo();
            $receiversInfo = $observer->getReceiversInfo();
            $templateId = ModuleGlobalConstants::DELIVEYBOY_NEW_REVIEW_EMAIL_TEMPLATE_ID;
            $this->operationHelper->sendEmail(
                $storeId,
                $templateVars,
                $senderInfo,
                $receiversInfo,
                $templateId
            );
        } catch (\Throwable $t) {
            $this->logger->debug($t->getMessage());
        }
    }
}
