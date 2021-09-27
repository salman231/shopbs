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
namespace Webkul\DeliveryBoy\Controller\Adminhtml\Graph;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Encryption\Helper\Security;
use Magento\Framework\HTTP\ZendClient;
use Psr\Log\LoggerInterface;

class GenerateGraph extends Action
{
    /**
     * @var \Magento\Framework\Controller\Result\RawFactory
     */
    protected $_resultRawFactory;

    /**
     * @var \Webkul\Mpreportsystem\Helper\Data
     */
    protected $_helperData;

    /**
     * @var ZendClient
     */
    protected $_zendClient;

    /**
     * @var LoggerInterface
     */
    protected $_logger;

    /**
     * @var \Webkul\Mpreportsystem\Helper\Operation
     */
    private $operationHelper;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Base64Json
     */
    private $base64JsonDecoder;

    /**
     * @param Context $context
     * @param \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
     * @param \Webkul\DeliveryBoy\Helper\Data $helperData
     * @param ZendClient $zendClient
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
        \Webkul\DeliveryBoy\Helper\Data $helperData,
        ZendClient $zendClient,
        LoggerInterface $logger,
        \Webkul\DeliveryBoy\Helper\Operation $operationHelper,
        \Magento\Framework\Serialize\Serializer\Base64Json $base64JsonDecoder
    ) {
        parent::__construct($context);
        $this->_resultRawFactory = $resultRawFactory;
        $this->_helperData = $helperData;
        $this->_zendClient = $zendClient;
        $this->_logger = $logger;
        $this->operationHelper = $operationHelper;
        $this->base64JsonDecoder = $base64JsonDecoder;
    }

    /**
     * @return \Magento\Framework\Controller\Result\Raw
     */
    public function execute()
    {
        $errorMessage = __('invalid request');
        $httpCode = 400;
        $getEncodedParamData = $this->_request->getParam('param_data');
        $getEncryptedHashData = $this->_request->getParam('encrypted_data');
        $resultRaw = $this->_resultRawFactory->create();

        if ($getEncodedParamData && $getEncryptedHashData) {
            $newEncryptedHashData = $this->operationHelper
                ->getChartEncryptedHashData($getEncodedParamData);
            if (Security::compareStrings(
                $newEncryptedHashData,
                $getEncryptedHashData
            )
            ) {
                $params = null;
                $paramsJson = $this->base64JsonDecoder->unserialize(urldecode($getEncodedParamData));
                if ($params) {
                    try {
                        $httpZendClient = $this->_zendClient;
                        $response = $httpZendClient->setUri(
                            'http://chart.apis.google.com/chart'
                        )->setParameterGet(
                            $params
                        )->setConfig(
                            ['timeout' => 5]
                        )->request(
                            'GET'
                        );
                        $responseHeaders = $response->getHeaders();
                        $resultRaw->setHeader(
                            'Content-type',
                            $responseHeaders['Content-type']
                        )
                            ->setContents($response->getBody());

                        return $resultRaw;
                    } catch (\Exception $e) {
                        $this->_logger->critical($e);
                        $errorMessage = __('see error log for details');
                        $httpCode = 503;
                    }
                }
            }
        }
        $resultRaw->setHeader('Content-Type', 'text/plain; charset=UTF-8')
            ->setHttpResponseCode($httpCode)
            ->setContents(__('Service unavailable: %1', $errorMessage));

        return $resultRaw;
    }
}
