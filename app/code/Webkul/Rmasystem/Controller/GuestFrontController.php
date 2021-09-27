<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Rmasystem
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Rmasystem\Controller;

use Magento\Framework\App\Action\Context;
use Magento\Customer\Model\Session;
use Magento\Framework\Filesystem\Io\File;
use Magento\Framework\App\Filesystem\DirectoryList;
use Webkul\Rmasystem\Model\ResourceModel\Rmaitem\CollectionFactory as ItemCollectionFactory;
use Magento\Sales\Model\OrderRepository;
use Webkul\Rmasystem\Api\AllRmaRepositoryInterface;
use Webkul\Rmasystem\Api\Data\AllrmaInterfaceFactory;
use Magento\Framework\Session\SessionManager;
use Magento\ImportExport\Model\ResourceModel\Helper as ResourceModelHelper;

class GuestFrontController extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Webkul\Rmasystem\Helper\Email
     */
    protected $_emailHelper;

    /**
     * @var \Webkul\Rmasystem\Helper\Data
     */
    protected $helper;

    /**
     * @var File
     */
    protected $_fileIo;

    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $_filesystem;

    /**
     * File Uploader factory.
     *
     * @var \Magento\MediaStorage\Model\File\UploaderFactory
     */
    protected $_fileUploaderFactory;

    /**
     * @var ItemCollectionFactory
     */
    protected $itemCollectionFactory;

    /**
     * @var \Webkul\Rmasystem\Api\Data\RmaitemInterfaceFactory
     */
    protected $rmaItemDataFactory;

    /**
     * @var \Webkul\Rmasystem\Api\RmaitemRepositoryInterface
     */
    protected $rmaItemRepository;

    /**
     * @var \Webkul\Rmasystem\Api\Data\ConversationInterfaceFactory
     */
    protected $conversationDataFactory;

    /**
     * @var \Webkul\Rmasystem\Api\ConversationRepositoryInterface
     */
    protected $conversationRepository;

    /**
     * @var OrderRepository
     */
    protected $orderRepository;

    /**
     * @var AllRmaRepositoryInterface
     */
    protected $rmaRepository;

    /**
     * @var AllrmaInterfaceFactory
     */
    protected $rmaFactory;

    /**
     * @var Session
     */
    protected $session;

    /**
     * @var ResourceModelHelper
     */
    protected $resourceModelHelper;

    /**
     * @var \Webkul\Rmasystem\Api\Data\ReasonRepositoryInterface
     */
    protected $reasonRepository;

    /**
     * @var [type]
     */
    protected $fieldValue;
    /**
     * @param Context                                                 $context
     * @param Session                                                 $customerSession
     * @param WebkulRmasystemHelperEmail                              $emailHelper
     * @param WebkulRmasystemHelperData                               $helper
     * @param MagentoFrameworkFilesystem                              $filesystem
     * @param Magento\Media\Storage\Model\FileUploaderFactory         $fileUploaderFactory
     * @param ItemCollectionFactory                                   $itemCollectionFactory
     * @param Webkul\Rmasystem\Api\Data\RmaitemInterfaceFactory       $rmaItemDataFactory
     * @param Webkul\Rmasystem\Api\RmaitemRepositoryInterface         $rmaItemRepository
     * @param Webkul\Rmasystem\Api\Data\ConversationInterfaceFactory  $conversationDataFactory
     * @param Webkul\Rmasystem\Api\ConversationRepositoryInterface    $conversationRepository
     * @param OrderRepository                                         $orderRepository
     * @param AllRmaRepositoryInterface                               $rmaRepository
     * @param AllrmaInterfaceFactory                                  $rmaFactory
     * @param ResourceModelHelper                                     $resourceModelHelper
     * @param SessionManager                                          $session
     * @param File                                                    $fileIo
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        \Webkul\Rmasystem\Helper\Email $emailHelper,
        \Webkul\Rmasystem\Helper\Data $helper,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory,
        ItemCollectionFactory $itemCollectionFactory,
        \Webkul\Rmasystem\Api\Data\RmaitemInterfaceFactory $rmaItemDataFactory,
        \Webkul\Rmasystem\Api\RmaitemRepositoryInterface $rmaItemRepository,
        \Webkul\Rmasystem\Api\Data\ConversationInterfaceFactory $conversationDataFactory,
        \Webkul\Rmasystem\Api\ConversationRepositoryInterface $conversationRepository,
        OrderRepository $orderRepository,
        AllRmaRepositoryInterface $rmaRepository,
        AllrmaInterfaceFactory $rmaFactory,
        ResourceModelHelper $resourceModelHelper,
        SessionManager $session,
        File $fileIo,
        \Webkul\Rmasystem\Model\FieldvalueFactory $fieldValueFactory,
        \Webkul\Rmasystem\Api\ReasonRepositoryInterface $reasonRepository
    ) {

        $this->_customerSession = $customerSession;
        $this->_fileIo = $fileIo;
        $this->_emailHelper = $emailHelper;
        $this->helper = $helper;
        $this->_filesystem = $filesystem;
        $this->_fileUploaderFactory = $fileUploaderFactory;
        $this->itemCollectionFactory = $itemCollectionFactory;
        $this->rmaItemDataFactory = $rmaItemDataFactory;
        $this->rmaItemRepository = $rmaItemRepository;
        $this->conversationDataFactory = $conversationDataFactory;
        $this->conversationRepository = $conversationRepository;
        $this->orderRepository = $orderRepository;
        $this->rmaRepository = $rmaRepository;
        $this->rmaFactory = $rmaFactory;
        $this->resourceModelHelper = $resourceModelHelper;
        $this->session = $session;
        $this->fieldValue = $fieldValueFactory;
        $this->reasonRepository = $reasonRepository;
        parent::__construct($context);
    }

    public function execute()
    {
      /** child controllers will use it */
    }

    protected function saveRmaProductImage($numberOfImages, $lastRmaId)
    {
        $imageArray = [];
        try {
            if ($numberOfImages > 0) {
                $path = $this->helper->getBaseDir($lastRmaId);
                for ($i = 0; $i < $numberOfImages; $i++) {
                    $fileId = "related_images[$i]";
                    $this->uploadImage($fileId, $path, $imageArray);
                }
            }
            return $imageArray;
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * Notify message that rma created.
     * @param  int $rmaId
     */
    public function saveRmaHistory($rmaId, $message)
    {
        $conversationModel = $this->conversationDataFactory->create()
          ->setRmaId($rmaId)
          ->setMessage($message)
          ->setCreatedAt(time())
          ->setSender('default');
        try {
            $this->conversationRepository->save($conversationModel);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
            return $resultRedirect->setPath('*/*/index');
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
            $this->messageManager->addException($e, __('Something went wrong while saving the Message.'));
            return $resultRedirect->setPath('*/*/index');
        }
    }

    /**
     * Upload Image of Rma
     *
     * @param string $fileId
     * @param string $uploadPath
     * @param int $count
     */
    protected function uploadImage($fileId, $path, &$imageArray)
    {
        $extArray = ['jpg','jpeg','gif','png', 'svg'];
        try {
            /** @var $uploader \Magento\MediaStorage\Model\File\Uploader */
            $uploader = $this->_fileUploaderFactory->create(['fileId' => $fileId]);
            $uploader->setAllowedExtensions($extArray);
            $uploader->setAllowRenameFiles(true);
            $uploader->setAllowCreateFolders(true);
            $result = $uploader->save($path);
            $imageArray[$result['file']] = $result['file'];
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
            $this->messageManager->addException($e, __($e->getMessage()));
            throw new \Exception($e->getMessage());
        }
    }
}
