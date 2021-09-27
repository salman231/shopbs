<?php
/**
 * Webkul Software.
 *
 *
 *
 * @category  Webkul
 * @package   Webkul_MobikulCore
 * @author    Webkul <support@webkul.com>
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html ASL Licence
 * @link      https://store.webkul.com/license.html
 */

namespace Webkul\MobikulCore\Controller\Adminhtml\Bannerimage;

use Magento\Framework\Controller\ResultFactory;

/**
 * Class to upload banner images
 */
class Upload extends \Webkul\MobikulCore\Controller\Adminhtml\Bannerimage
{
    /**
     * Execute function for class Upload
     */
    public function execute()
    {
        $result = [];
        if ($this->getRequest()->isPost()) {
            try {
                $fields = $this->getRequest()->getParams();
                $files = $this->getRequest()->getFiles();
                $bannerimageDirPath = $this->mediaDirectory->getAbsolutePath("mobikul/bannerimages");
                if (!$this->fileDriver->isExists($bannerimageDirPath)) {
                    $this->fileDriver->createDirectory($bannerimageDirPath, 0777);
                }
                $baseTmpPath = "mobikul/bannerimages/";
                $target = $this->mediaDirectory->getAbsolutePath($baseTmpPath);
                try {
                    $uploader = $this->fileUploaderFactory->create(["fileId" => "mobikul_bannerimage[filename]"]);
                    $fileName = $files["mobikul_bannerimage"]["filename"]["name"];
                    $ext = substr($fileName, strrpos($fileName, ".") + 1);
                    $editedFileName = "File-".time().".".$ext;
                    $uploader->setAllowedExtensions(["jpg", "jpeg", "gif", "png"]);
                    $uploader->setAllowRenameFiles(true);
                    $result = $uploader->save($target, $editedFileName);
                    if (!$result) {
                        $result = [
                            "error" => __("File can not be saved to the destination folder."),
                            "errorcode" => ""
                        ];
                    }
                    if (isset($result["file"])) {
                        try {
                            $result["tmp_name"] = str_replace("\\", "/", $result["tmp_name"]);
                            $result["path"] = str_replace("\\", "/", $result["path"]);
                            $result["url"] = $this->storeManager
                                ->getStore()
                                ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA).$this->getFilePath(
                                    $baseTmpPath,
                                    $result["file"]
                                );
                            $result["name"] = $result["file"];
                        } catch (\Exception $e) {
                            $result = ["error"=>$e->getMessage(), "errorcode"=>$e->getCode()];
                        }
                    }
                    $result["cookie"] = [
                        "name" => $this->_getSession()->getName(),
                        "path" => $this->_getSession()->getCookiePath(),
                        "value" => $this->_getSession()->getSessionId(),
                        "domain" => $this->_getSession()->getCookieDomain(),
                        "lifetime" => $this->_getSession()->getCookieLifetime()
                    ];
                } catch (\Exception $e) {
                    $result = ["error"=>$e->getMessage(), "errorcode"=>$e->getCode()];
                }
            } catch (\Exception $e) {
                $result = ["error"=>$e->getMessage(), "errorcode"=>$e->getCode()];
            }
        }
        return $this->resultFactory->create(ResultFactory::TYPE_JSON)->setData($result);
    }

    /**
     * Function to get fulepath
     *
     * @return string
     */
    public function getFilePath($path, $imageName)
    {
        return rtrim($path, "/") . "/" . ltrim($imageName, "/");
    }
}
