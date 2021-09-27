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

use Webkul\DeliveryBoy\Model\Deliveryboy;
use Webkul\DeliveryBoy\Helper\Data;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Mail\Template\TransportBuilder;
use Psr\Log\LoggerInterface;
use Magento\Framework\Translate\Inline\StateInterface;
use Webkul\DeliveryBoy\Helper\ModuleGlobalConstants;

class SendDeliveyboyNewAccountEmailObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var TransportBuilder
     */
    private $transportBuilder;

    /**
     * @var StateInterface
     */
    private $inlineTranslation;
    
    /**
     * @var Data
     */
    private $deliveryboyHelper;
    
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param StoreManagerInterface $storeManager
     * @param TransportBuilder $transportBuilder
     * @param StateInterface $inlineTranslation
     * @param Data $deliveryboyHelper
     * @param LoggerInterface $logger
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        TransportBuilder $transportBuilder,
        StateInterface $inlineTranslation,
        Data $deliveryboyHelper,
        LoggerInterface $logger
    ) {
        $this->storeManager = $storeManager;
        $this->transportBuilder = $transportBuilder;
        $this->inlineTranslation = $inlineTranslation;
        $this->deliveryboyHelper = $deliveryboyHelper;
        $this->logger = $logger;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try {
            $deliveryboy = $observer->getDeliveryboy();
            $storeId = $observer->getStoreId();
            
            $store = $storeId
            ? $this->storeManager->getStore($storeId)
            : $this->storeManager->getDefaultStoreView();

            $storeId = $store->getId();

            $templateVariables = [
                'deliveryboy' => $deliveryboy,
                'store' => $store,
            ];
            
            $this->inlineTranslation->suspend();

            $senderInfo = $observer->getSenderInfo();
            if (empty($senderInfo)) {
                $senderInfo = [
                    "name"  => ModuleGlobalConstants::DEFAULT_ADMIN_NAME,
                    "email" => $this->deliveryboyHelper->getConfigData(
                        ModuleGlobalConstants::DEFAULT_ADMIN_EMAIL_XML_PATH
                    )
                ];
            }
            $receiverInfo = [
                "name"  => $deliveryboy->getName(),
                "email" => $deliveryboy->getEmail()
            ];
            $this->transportBuilder
                ->setTemplateIdentifier(ModuleGlobalConstants::DELIVEYBOY_NEW_ACCOUNT_EMAIL_TEMPLATE_ID)
                ->setTemplateOptions(
                    [
                        "area"  => \Magento\Framework\App\Area::AREA_FRONTEND,
                        "store" => $storeId,
                    ]
                )
                ->setTemplateVars($templateVariables)
                ->setFrom($senderInfo)
                ->addTo($receiverInfo["email"], $receiverInfo["name"]);
            $transport = $this->transportBuilder->getTransport();
            $transport->sendMessage();
            $this->inlineTranslation->resume();
        } catch (\Throwable $t) {
            $this->logger->debug($t->getMessage());
        }
    }
}
