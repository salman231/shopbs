<?php

namespace Magemonkey\Categorythumb\Controller\Adminhtml\Category\Imageuploader\Categorythumb;

use Magento\Framework\Controller\ResultFactory;

/**
 * Class Upload
 */
class Categorythumb extends \Magento\Catalog\Controller\Adminhtml\Category\Image\Upload
{
    /**
     * Image uploader
     *
     * @var \Magento\Catalog\Model\ImageUploader
     */
    protected $imageUploader;

    /**
     * Check admin permissions for this controller
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magento_Catalog::categories');
    }

    /**
     * Upload file controller action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        try {
            $result = $this->imageUploader->saveFileToTmpDir('categorythumb');

            $result['cookie'] = [
                'name' => $this->_getSession()->getName(),
                'value' => $this->_getSession()->getSessionId(),
                'lifetime' => $this->_getSession()->getCookieLifetime(),
                'path' => $this->_getSession()->getCookiePath(),
                'domain' => $this->_getSession()->getCookieDomain(),
            ];
        } catch (\Exception $e) {
            $result = ['error' => $e->getMessage(), 'errorcode' => $e->getCode()];
        }
        return $this->resultFactory->create(ResultFactory::TYPE_JSON)->setData($result);
    }
}