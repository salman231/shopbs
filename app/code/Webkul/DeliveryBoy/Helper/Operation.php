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
namespace Webkul\DeliveryBoy\Helper;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Config\ConfigOptionsListConstants;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Mail\Template\TransportBuilder;
use Psr\Log\LoggerInterface;
use Magento\Framework\Translate\Inline\StateInterface;
use Webkul\DeliveryBoy\Model\ResourceModel\Token\Collection as TokenResourceCollection;

class Operation extends \Magento\Framework\App\Helper\AbstractHelper
{
    const HASH_VERSION_MD5 = 'md5';
    
    /**
     * @var \Magento\Framework\HTTP\Client\Curl
     */
    private $curl;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    private $jsonHelper;

    /**
     * @var Magento\Framework\App\DeploymentConfig
     */
    private $deploymentConfig;

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
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\HTTP\Client\Curl $curl
     * @param LoggerInterface $logger
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Magento\Framework\App\DeploymentConfig $deploymentConfig
     * @param StoreManagerInterface $storeManager
     * @param TransportBuilder $transportBuilder
     * @param StateInterface $inlineTranslation
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\HTTP\Client\Curl $curl,
        LoggerInterface $logger,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Framework\App\DeploymentConfig $deploymentConfig,
        StoreManagerInterface $storeManager,
        TransportBuilder $transportBuilder,
        StateInterface $inlineTranslation
    ) {
        parent::__construct($context);

        $this->curl = $curl;
        $this->logger = $logger;
        $this->jsonHelper = $jsonHelper;
        $this->deploymentConfig = $deploymentConfig;
        $this->storeManager = $storeManager;
        $this->transportBuilder = $transportBuilder;
        $this->inlineTranslation = $inlineTranslation;
    }

    /**
     * @param array $headers
     * @param array $payload
     * @param string $url
     * @return array
     */
    public function send(
        array $headers = [],
        array $payload = [],
        string $url = "https://fcm.googleapis.com/fcm/send"
    ): array {
        try {
            $this->curl->setOption(CURLOPT_URL, $url);
            $this->curl->setOption(CURLOPT_POST, true);
            $this->curl->setOption(CURLOPT_HTTPHEADER, $headers);
            $this->curl->setOption(CURLOPT_RETURNTRANSFER, true);
            $this->curl->setOption(CURLOPT_SSL_VERIFYPEER, $url);
            $this->curl->setOption(CURLOPT_POSTFIELDS, json_encode($payload));
            $this->curl->post($url, []);
            $response = $this->curl->getBody();
            $this->logger->info("Notification: " . $response);
            $this->logger->info("PayLoad: " . $this->jsonHelper->jsonEncode($payload));

            return $this->jsonHelper->jsonDecode($response);
        } catch (\Throwable $t) {
            $this->logger->debug($t->getMessage());

            return [];
        }
    }

    /**
     * @param string $data
     * @return string
     */
    public function getMd5Hash(string $data)
    {
        return hash(self::HASH_VERSION_MD5, $data);
    }

    /**
     * @param string $data
     * @return string
     */
    public function getChartEncryptedHashData(string $data)
    {
        $deploymentConfigDate = $this->deploymentConfig->get(
            ConfigOptionsListConstants::CONFIG_PATH_INSTALL_DATE
        );
        return $this->getMd5Hash($data . $deploymentConfigDate);
    }

    /**
     * @param int $storeId
     * @param array $templateVars
     * @param array $sendersInfo
     * @param array $receiversInfo
     * @param string $templateId
     * @return bool
     */
    public function sendEmail(
        int $storeId,
        array $templateVars,
        array $sendersInfo,
        array $receiversInfo,
        string $templateId
    ): bool {
        try {
            $store = $storeId
            ? $this->storeManager->getStore($storeId)
            : $this->storeManager->getDefaultStoreView();
            $storeId = $store->getId();
            
            $this->inlineTranslation->suspend();
            $this->transportBuilder
                ->setTemplateIdentifier($templateId)
                ->setTemplateOptions(
                    [
                        "area"  => \Magento\Framework\App\Area::AREA_FRONTEND,
                        "store" => $storeId,
                    ]
                )
                ->setTemplateVars($templateVars)
                ->setFrom($sendersInfo)
                ->addTo($receiversInfo["email"], $receiversInfo["name"]);
            $transport = $this->transportBuilder->getTransport();
            $transport->sendMessage();
            $this->inlineTranslation->resume();
        } catch (\Throwable $t) {
            $this->logger->debug($t->getMessage());

            return false;
        }

        return true;
    }

    /**
     * @param string $authKey
     * @param array $fields
     * @param array $message
     * @param TokenResourceCollection $tokenCollection
     * @return void
     */
    public function sendNotificationToSubscribedUsers(
        string $authKey,
        array $fields,
        array $message,
        TokenResourceCollection $tokenCollection
    ) {
        try {
            $headers = [
                "Authorization: key=" . $authKey,
                "Content-Type: application/json",
            ];
            foreach ($tokenCollection as $eachToken) {
                $fields['to'] = $eachToken->getToken();
                if ($eachToken->getOs() == "ios") {
                    $fields["notification"] = $message;
                }
                $result = $this->send($headers, $fields);
                if (count($result) !== 0) {
                    if ($result["success"] == 0 && $result["failure"] == 1) {
                        $eachToken->delete();
                    }
                }
            }
        } catch (\Throwable $e) {
            $this->logger->debug($e->getMessage());
        }
    }
}
