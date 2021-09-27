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

class Start extends \Magento\Backend\App\Action
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
     * @var \Magento\Backend\Model\Auth\SessionFactory
     */
    private $authSessionFactory;

    /**
     * @var AgentDataFactory
     */
    protected $agentDataFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Backend\Model\Auth\Session $authSessionFactory
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Framework\App\Filesystem\DirectoryList $directoryList
     * @param AgentDataFactory $agentDataFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Backend\Model\Auth\SessionFactory $authSessionFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList,
        \Magento\Framework\Shell $shell,
        \Magento\Framework\Filesystem\Driver\File $fileDriver,
        AgentDataFactory $agentDataFactory
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->authSessionFactory = $authSessionFactory;
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
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/mptest.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info('==============');
        $node = $this->shell->execute('whereis node');
        $logger->info($node);
        // die('sdbd');
        // $rootPath = $this->directoryList->getRoot();
        
        // $logger->info($rootPath);
        try {
               $logger->info("1=1");
            $response->setError(false);
            if (!$this->isServerRunning()) {
                $response->setRoot($this->directoryList->getRoot());
                $rootPath = $this->directoryList->getRoot();
                $node = $this->shell->execute('whereis node');
                $nodePath = explode(' ', $node);
                $logger->info("1=2");
                // $logger->info($nodePath);
                // $logger->info($rootPath);
                if (!isset($nodePath[1])) {
                    $node = $this->shell->execute('whereis nodejs');
                    $logger->info("1=3");
                    $nodePath = explode(' ', $node);
                }
                if (isset($nodePath[1])) {
                    $logger->info("1=4");
                    $this->shell->execute($nodePath[1].' '.$rootPath.'/app.js' . " > /dev/null &");
                    $logger->info($nodePath[1]);
                    $logger->info($rootPath);
                    $logger->info($nodePath[1].' '.$rootPath.'/app.js' . " > /dev/null &");
                    $logger->info("1=5");
                    $response->setMessage(
                        __('Server Running.')
                    );
                } else {
                    $response->setError(true);
                    $response->setMessage(
                        __('Node path can not be found, make sure Node is installed on this server.')
                    );
                }
            } elseif (!$response->getError()) {
                $logger->info("1=6");
                $response->setMessage(
                    __('Node server already running.')
                );
            }

            $agentId = $this->authSessionFactory->create()->getUser()->getId();
            $agentDataModel = $this->agentDataFactory->create()
                ->getCollection();
            $entityId = 0;
            $logger->info("1=7");
            if ($agentDataModel->getSize()) {
                $entityId = $agentDataModel->getFirstItem()->getEntityId();
            }
            if ($entityId) {
                $agentDataModel = $this->agentDataFactory->create()->load($entityId);
                $agentDataModel->setAgentId($agentId);
                $agentDataModel->setId($entityId);
                $agentDataModel->save();
            } else {
                $user = $this->authSessionFactory->create()->getUser();
                $agentDataModel = $this->agentDataFactory->create()
                                                ->setAgentId($agentId)
                                                ->setAgentUniqueId($this->generateUniqueId())
                                                ->setAgentEmail($user->getEmail())
                                                ->setAgentName($user->getFirstName(). ' '.$user->getLastName())
                                                ->save();
            }
        } catch (\Exception $e) {
            $response->setError(true);
            $response->setMessage(
                __('Something went wrong.')
            );
        }
        return $this->resultJsonFactory->create()->setJsonData($response->toJson());
    }

    /**
     * check if the node server is already running on a specific port.
     *
     * @return bool
     */
    public function isServerRunning()
    {
        $response = new \Magento\Framework\DataObject();
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/mptest11.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info('=======11=======');

        $result = false;
        $host = $this->getRequest()->getParam('hostname');
        $port = $this->getRequest()->getParam('port');
        try {
            $logger->info('=======12=======');
            $connection = fsockopen($host, $port);
            $logger->info('=======12.1=======');
            if (is_resource($connection)) {
                $logger->info('=======13=======');
                $result = true;
                $this->fileDriver->fileClose($connection);
            } else {
                $logger->info('=======14=======');
                $result = false;
            }
        } catch (\Exception $e) {
            $logger->info('=======15 ======='.$e->getMessage());
            $result = false;
        }
        $logger->info('=======16=======');
        return $result;
    }

    /**
     * Generate Unique Id
     *
     * @return int
     */
    public function generateUniqueId()
    {
        $alphabet = 'abcdefghijklmnopqrstuvwxyz1234567890ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $pass = [];
        $alphaLength = strlen($alphabet) - 1;
        for ($i = 0; $i < 8; $i++) {
            $n = rand(0, $alphaLength);
            $pass[] = $alphabet[$n];
        }
        $id = 'wk'.implode($pass);
        return $id;
    }
}
