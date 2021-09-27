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

namespace Webkul\DeliveryBoy\Controller\Adminhtml\Deliveryboy;

use Magento\Framework\Controller\ResultFactory;

class Upload extends \Webkul\DeliveryBoy\Controller\Adminhtml\Deliveryboy
{
    /**
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $result = [];
        if ($this->getRequest()->isPost()) {
            try {
                $fields = $this->getRequest()->getParams();
                $files = $this->getRequest()->getFiles();
                $baseTmpPath  = "deliveryboy/images/";
                $target = $this->mediaDirectory->getAbsolutePath($baseTmpPath);
                if (!$this->fileDriver->isExists($target)) {
                    $this->fileDriver->createDirectory($target, 0777);
                }
                try {
                    $uploader = $this->fileUploaderFactory->create(["fileId" => "expressdelivery_deliveryboy[image]"]);
                    $fileName = $files["expressdelivery_deliveryboy"]["image"]["name"];
                    $ext = substr($fileName, strrpos($fileName, ".") + 1);
                    $editedFileName = "File-" . time() . "." . $ext;
                    $uploader->setAllowedExtensions(["jpg", "jpeg", "gif", "png"]);
                    $uploader->setAllowRenameFiles(true);
                    $result   = $uploader->save($target, $editedFileName);
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
                                ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) .
                                    $this->getFilePath($baseTmpPath, $result["file"]);
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
     * Concatenate and return path and name using slash
     *
     * @param string $path
     * @param string $imageName
     * @return string
     */
    public function getFilePath($path, $imageName)
    {
        return rtrim($path, "/") . "/" . ltrim($imageName, "/");
    }
}
