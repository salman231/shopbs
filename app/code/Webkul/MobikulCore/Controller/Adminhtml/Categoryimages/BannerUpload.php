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

namespace Webkul\MobikulCore\Controller\Adminhtml\Categoryimages;

use Magento\Framework\Controller\ResultFactory;

/**
 * Class BannerUpload for Categoryimages
 */
class BannerUpload extends \Webkul\MobikulCore\Controller\Adminhtml\Categoryimages
{
    /**
     * Execute function for class BannerUpload
     *
     * @return resultFactory
     */
    public function execute()
    {
        $result = [];
        if ($this->getRequest()->isPost()) {
            try {
                $fields = $this->getRequest()->getParams();
                $categoryBannerImageDirPath = $this->mediaDirectory->getAbsolutePath("mobikul/categoryimages/banner");
                if (!$this->fileDriver->isExists($categoryBannerImageDirPath)) {
                    $this->fileDriver->createDirectory($categoryBannerImageDirPath, 0777);
                }
                $baseTmpPath = "mobikul/categoryimages/banner/";
                $target = $this->mediaDirectory->getAbsolutePath($baseTmpPath);
                try {
                    $uploader = $this->fileUploaderFactory->create(["fileId"=>"mobikul_categoryimages[banner]"]);
                    $uploader->setAllowedExtensions(["jpg", "jpeg", "gif", "png"]);
                    $uploader->setAllowRenameFiles(true);
                    $result = $uploader->save($target);
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
                            $result["url"]  = $this->storeManager
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
                        "value" => $this->_getSession()->getSessionId(),
                        "path" => $this->_getSession()->getCookiePath(),
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
     * Function to get file path
     *
     * @param string $path      path
     * @param string $imageName imageName
     *
     * @return bool
     */
    public function getFilePath($path, $imageName)
    {
        return rtrim($path, "/") . "/" . ltrim($imageName, "/");
    }
}
