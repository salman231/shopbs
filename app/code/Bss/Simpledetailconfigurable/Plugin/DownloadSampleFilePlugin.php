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
namespace Bss\Simpledetailconfigurable\Plugin;

use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Class DownloadSampleFilePlugin
 *
 * @package Bss\Simpledetailconfigurable\Plugin
 * @SuppressWarnings(PHPMD.AllPurposeAction)
 */
class DownloadSampleFilePlugin extends \Magento\ImportExport\Controller\Adminhtml\Import\Download
{
    /**
     * Module dir
     */
    const PRODUCT_ATTRIBUTES_SAMPLE_FILE = 'Bss_Simpledetailconfigurable';

    /**
     * @param \Magento\ImportExport\Controller\Adminhtml\Import\Download $subject
     * @param callable $proceed
     * @return \Magento\Framework\Controller\Result\Raw|\Magento\Framework\Controller\Result\Redirect
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundExecute(
        \Magento\ImportExport\Controller\Adminhtml\Import\Download $subject,
        $proceed
    ) {
        $fileName = $this->getRequest()->getParam('filename') . '.csv';
        if ($this->getRequest()->getParam('filename')=='sdcp_preselect') {
            try {
                $moduleDir = $this->componentRegistrar->getPath(
                    ComponentRegistrar::MODULE,
                    self::PRODUCT_ATTRIBUTES_SAMPLE_FILE
                );
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
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            }
        }
        return $proceed();
    }
}
