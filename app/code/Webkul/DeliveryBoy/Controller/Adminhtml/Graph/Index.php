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
use Magento\Framework\HTTP\ZendClient;
use Psr\Log\LoggerInterface;

class Index extends Action
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
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;
    
    /**
     * @param Context $context
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
     * @param \Webkul\DeliveryBoy\Helper\Data $helperData
     * @param ZendClient $zendClient
     * @param LoggerInterface $logger
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     */
    public function __construct(
        Context $context,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
        \Webkul\DeliveryBoy\Helper\Data $helperData,
        ZendClient $zendClient,
        LoggerInterface $logger,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
    ) {
        parent::__construct($context);
        $this->_resultRawFactory = $resultRawFactory;
        $this->_helperData = $helperData;
        $this->_zendClient = $zendClient;
        $this->request = $request;
        $this->_logger = $logger;
        $this->resultJsonFactory = $resultJsonFactory;
    }

    /**
     * Request to get seller statistics graph image to the web-service.
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $params = $this->request->getParams();
        $data = $params ?? [];
        $result = $this->_helperData->getSalesAmount($data["data"]);
        $resultSet = $this->resultJsonFactory->create();
        return $resultSet->setData($result);
    }
}
