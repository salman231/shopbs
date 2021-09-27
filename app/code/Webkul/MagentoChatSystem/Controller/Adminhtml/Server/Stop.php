<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MagentoChatSystem
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MagentoChatSystem\Controller\Adminhtml\Server;

use Webkul\MagentoChatSystem\Model\AgentDataFactory;

class Stop extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var \Magento\Framework\App\Filesystem\DirectoryList
     */
    protected $directoryList;

    /**
     * @var CustomerSession
     */
    private $authSession;

    /**
     * @var AgentDataFactory
     */
    protected $agentDataFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Backend\Model\Auth\Session $authSession
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Framework\App\Filesystem\DirectoryList $directoryList
     * @param AgentDataFactory $agentDataFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList,
        \Magento\Framework\Shell $shell,
        \Magento\Framework\Filesystem\Driver\File $fileDriver,
        AgentDataFactory $agentDataFactory
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->authSession = $authSession;
        $this->directoryList = $directoryList;
        $this->agentDataFactory = $agentDataFactory;
        $this->shell = $shell;
        $this->fileDriver = $fileDriver;
    }

    /**
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $response = new \Magento\Framework\DataObject();
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/mpteststop.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info('=======11=======');

        $response = new \Magento\Framework\DataObject();
        $response->setError(false);
        $data = $this->getRequest()->getParams();
        $logger->info('=======11======='.json_encode($data));
        $response->setRoot($this->directoryList->getRoot());
        $rootPath = $this->directoryList->getRoot();
        $logger->info('=======12=======');
        $getUserPath = $this->shell->execute('whereis fuser');
        if ($getUserPath) {
            $logger->info('=======13=======');
            $getUserPath = explode(' ', $getUserPath);
            if (isset($getUserPath[1])) {
                $logger->info('=======14=======');
                $stopServer = $this->shell->execute($getUserPath[1].' -k '.$data['port'].'/tcp');
                $logger->info('=======15=======');
            }
            $agentId = $this->authSession->getUser()->getId();
            $logger->info('=======16=======');
            $agentDataModel = $this->agentDataFactory->create()
                ->getCollection()
                ->addFieldToFilter('agent_id', ['eq' => $agentId]);
            $entityId = 0;
            if ($agentDataModel->getSize()) {
                $logger->info('=======17=======');
                $entityId = $agentDataModel->getLastItem()->getEntityId();
            }
            if ($entityId) {
                $logger->info('=======18=======');
                $agentDataModel = $this->agentDataFactory->create()->load($entityId);
                $agentDataModel->setChatStatus(1);
                $agentDataModel->setId($entityId);
                $agentDataModel->save();
            }
        }
        return $this->resultJsonFactory->create()->setJsonData($response->toJson());
    }

    /**
     * Check Server Runing or not
     *
     * @param string $host
     * @param integer $port
     * @param integer $timeout
     * @return boolean
     */
    public function isServerRunning($host, $port = 80, $timeout = 6)
    {
        $response = new \Magento\Framework\DataObject();
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/mpteststop-1.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info('=======11=======');

        $result = false;
        try {
            $logger->info('=======12=======');
            $connection = fsockopen($host, $port);
            $logger->info('=======13=======');
            if (is_resource($connection)) {
                $result = true;
                $logger->info('=======14=======');
                $this->fileDriver->fileClose($connection);
                $logger->info('=======15=======');
            } else {
                $result = false;
                $logger->info('=======16=======');
            }
        } catch (\Exception $e) {
            $logger->info('=======17======='.$e->getMessage());
            $result = false;
        }
        return $result;
    }
}
