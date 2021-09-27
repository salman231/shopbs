<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Mpreportsystem
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Mpreportsystem\Controller\Report;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result;
use Magento\Framework\Encryption\Helper\Security;
use Magento\Framework\HTTP\ZendClient;
use Psr\Log\LoggerInterface;
use Webkul\Mpreportsystem\Block\Mpreport as MpReportBlock;

/**
 * Webkul Marketplace Mpreportsystem Report Generatereport Controller.
 */
class Generatereport extends Action
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
     * @var Magento\Framework\HTTP\ZendClient
     */
    protected $_zendClient;

    /**
     * @var Psr\Log\LoggerInterface
     */
    protected $_logger;

    /**
     * @var Magento\Framework\Encryption\UrlCoder
     */
    protected $_urlCoder;

    /**
     * @param Context $context
     * @param Result\RawFactory $resultRawFactory
     * @param \Webkul\Mpreportsystem\Helper\Data $helperData
     * @param ZendClient $zendClient
     * @param LoggerInterface $logger
     * @param \Magento\Framework\Encryption\UrlCoder $urlCoder
     */
    public function __construct(
        Context $context,
        Result\RawFactory $resultRawFactory,
        \Webkul\Mpreportsystem\Helper\Data $helperData,
        ZendClient $zendClient,
        LoggerInterface $logger,
        \Magento\Framework\Encryption\UrlCoder $urlCoder
    ) {
        parent::__construct($context);
        $this->_resultRawFactory = $resultRawFactory;
        $this->_helperData = $helperData;
        $this->_zendClient = $zendClient;
        $this->_logger = $logger;
        $this->_urlCoder = $urlCoder;
    }

    /**
     * Request to get seller statistics graph image to the web-service.
     *
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
            $newEncryptedHashData = $this->_helperData
                ->getChartEncryptedHashData($getEncodedParamData);
            if (Security::compareStrings(
                $newEncryptedHashData,
                $getEncryptedHashData
            )) {
                $params = null;
                $paramsJson = $this->_urlCoder->decode(urldecode($getEncodedParamData));
                if ($paramsJson) {
                    $params = json_decode($paramsJson, true);
                }
                if ($params) {
                    try {
                        $httpZendClient = $this->_zendClient;
                        $response = $httpZendClient->setUri(
                            MpReportBlock::GOOGLE_API_URL
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
