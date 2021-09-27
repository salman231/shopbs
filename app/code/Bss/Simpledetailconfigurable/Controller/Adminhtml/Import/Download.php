<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_Simpledetailconfigurable
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\Simpledetailconfigurable\Controller\Adminhtml\Import;

use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Class Download
 *
 * @package Bss\Simpledetailconfigurable\Controller\Adminhtml\Import
 * @SuppressWarnings(PHPMD.AllPurposeAction)
 */
class Download extends \Magento\ImportExport\Controller\Adminhtml\Import\Download
{
    /**
     * Module name dir
     */
    const URL_REWRITE_SAMPLE_FILE = 'Bss_Simpledetailconfigurable';

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $fileName = $this->getRequest()->getParam('filename') . '.csv';
        if ($this->getRequest()->getParam('filename') == 'bss_sdcp') {
            $moduleDir = $this->componentRegistrar->getPath(
                ComponentRegistrar::MODULE,
                self::URL_REWRITE_SAMPLE_FILE
            );
        } else {
            $moduleDir = $this->componentRegistrar->getPath(ComponentRegistrar::MODULE, self::SAMPLE_FILES_MODULE);
        }
        $fileAbsolutePath = $moduleDir . '/Files/Sample/' . $fileName;
        $directoryRead = $this->readFactory->create($moduleDir);
        $filePath = $directoryRead->getRelativePath($fileAbsolutePath);

        if (!$directoryRead->isFile($filePath)) {
            $this->messageManager->addErrorMessage(__('There is no sample file for this entity.'));
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('*/import');
            return $resultRedirect;
        }

        $fileSize = isset($directoryRead->stat($filePath)['size'])
            ? $directoryRead->stat($filePath)['size'] : null;

        $this->fileFactory->create(
            $fileName,
            null,
            DirectoryList::VAR_DIR,
            'application/octet-stream',
            $fileSize
        );
        $resultRaw = $this->resultRawFactory->create();
        $resultRaw->setContents($directoryRead->readFile($filePath));
        return $resultRaw;
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Bss_Simpledetailconfigurable::sdcp_preselect_import');
    }
}
