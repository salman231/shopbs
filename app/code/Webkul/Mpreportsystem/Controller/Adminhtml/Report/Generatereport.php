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

namespace Webkul\Mpreportsystem\Controller\Adminhtml\Report;

use Magento\Backend\App\Action;
use Magento\Framework\Encryption\Helper\Security;
use Magento\Framework\Controller\Result;
use Magento\Framework\HTTP\ZendClient;
use Psr\Log\LoggerInterface;
use Webkul\Mpreportsystem\Block\Mpreport as MpReportBlock;

class Generatereport extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $_resultPageFactory;

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
     * @param Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param Result\RawFactory $resultRawFactory
     * @param \Webkul\Mpreportsystem\Helper\Data $helperData
     * @param ZendClient $zendClient
     * @param LoggerInterface $logger
     * @param \Magento\Framework\Encryption\UrlCoder $urlCoder
     */
    public function __construct(
        Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        Result\RawFactory $resultRawFactory,
        \Webkul\Mpreportsystem\Helper\Data $helperData,
        ZendClient $zendClient,
        LoggerInterface $logger,
        \Magento\Framework\Encryption\UrlCoder $urlCoder
    ) {
        $this->_resultPageFactory = $resultPageFactory;
        $this->_resultRawFactory = $resultRawFactory;
        $this->_helperData = $helperData;
        $this->_zendClient = $zendClient;
        $this->_logger = $logger;
        $this->_urlCoder = $urlCoder;
        parent::__construct($context);
    }

    /**
     * Check for is allowed
     *
     * @return void
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed(
            'Webkul_Mpreportsystem::mpreports'
        );
    }

     /**
      * Generate report action
      *
      * @return void
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
