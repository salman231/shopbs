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
namespace Webkul\Rmasystem\Controller\Adminhtml\Allrma;

use Magento\Backend\App\Action;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Filesystem\DirectoryList;
use Magento\Framework\App\Filesystem\DirectoryList as Directory;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Backend\App\Action\Context;

class DownloadFile extends Action
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;
    /**
     * @var \Magento\Framework\Filesystem\Directory
     */
    protected $_directoryList;

    /**
     * @var FileFactory
     */
    protected $_fileFactory;

    /**
     * @var \Webkul\Rmasystem\Model\AllrmaFactory
     */
    protected $rmaFactory;

    /**
     * @param Action\Context                  $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param DirectoryList                   $directoryList
     * @param LabelGenerator                  $labelGenerator
     * @param FileFactory                     $fileFactory
     */
    public function __construct(
        Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Webkul\Rmasystem\Model\AllrmaFactory $rmaFactory,
        \Webkul\Rmasystem\Helper\Data $helper,
        DirectoryList $directoryList,
        FileFactory $fileFactory
    ) {
        $this->_directoryList = $directoryList;
        $this->_customerSession = $customerSession;
        $this->_fileFactory = $fileFactory;
        $this->rmaFactory = $rmaFactory;
        $this->helper = $helper;
        parent::__construct($context);
    }

    /**
     * return label in pdf formate.
     *
     * @return ResponseInterface
     */
    public function execute()
    {
        $rmaId = $this->getRequest()->getParam('id');
        $fileName = $this->getRequest()->getParam('file_name');
        $filePath = $this->helper->getBaseDir($rmaId);
        if ($this->getRequest()->getParam('conv')) {
            $filePath = $this->helper->getConversationDir($rmaId);
        }
        return $this->_fileFactory->create(
            $fileName,
            file_get_contents($filePath.$fileName),
            Directory::MEDIA,
            'image/jpeg'
        );
    }

    /**
     * Check for is allowed
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Webkul_Rmasystem::update');
    }
}
