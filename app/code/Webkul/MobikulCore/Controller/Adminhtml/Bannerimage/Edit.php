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

use Webkul\MobikulCore\Controller\RegistryConstants;
use Webkul\MobikulCore\Api\Data\BannerimageInterface;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class to Edit banner Images
 */
class Edit extends \Webkul\MobikulCore\Controller\Adminhtml\Bannerimage
{
    public function execute()
    {
        $bannerimageId = $this->initCurrentBanner();
        $isExistingBanner = (bool)$bannerimageId;
        if ($isExistingBanner) {
            try {
                $bannerimageDirPath = $this->mediaDirectory->getAbsolutePath("mobikul/bannerimages");
                if (!$this->fileDriver->isExists($bannerimageDirPath)) {
                    $this->fileDriver->createDirectory($bannerimageDirPath, 0777);
                }
                $baseTmpPath = "";
                $target = $this->storeManager->getStore()->getBaseUrl(
                    \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
                ).$baseTmpPath;
                $bannerimageData = [];
                $bannerimageData["mobikul_bannerimage"] = [];
                $bannerimage = null;
                $bannerimage = $this->bannerimageRepository->getById($bannerimageId);
                $result = $bannerimage->getData();
                if (count($result)) {
                    $bannerimageData["mobikul_bannerimage"] = $result;
                    $bannerimageData["mobikul_bannerimage"]["filename"] = [];
                    $bannerimageData["mobikul_bannerimage"]["filename"][0] = [];
                    $bannerimageData["mobikul_bannerimage"]["filename"][0]["name"] = $result["filename"];
                    $bannerimageData["mobikul_bannerimage"]["filename"][0]["url"] = $target.$result["filename"];
                    $filePath = $this->mediaDirectory->getAbsolutePath($baseTmpPath).$result["filename"];
                    if ($this->fileDriver->isFile($filePath)) {
                        $bannerimageData["mobikul_bannerimage"]["filename"][0]["size"] =
                        $this->fileHelper->getFileSize($filePath);
                    } else {
                        $bannerimageData["mobikul_bannerimage"]["filename"][0]["size"] = 0;
                    }
                    $bannerimageData["mobikul_bannerimage"][BannerimageInterface::ID] = $bannerimageId;
                    $this->_getSession()->setBannerimageFormData($bannerimageData);
                } else {
                    $this->messageManager->addError(__("Requested banner doesn't exist"));
                    $resultRedirect = $this->resultRedirectFactory->create();
                    $resultRedirect->setPath("mobikul/bannerimage/index");
                    return $resultRedirect;
                }
            } catch (NoSuchEntityException $e) {
                $this->messageManager->addException($e, __("Something went wrong while editing the banner."));
                $resultRedirect = $this->resultRedirectFactory->create();
                $resultRedirect->setPath("mobikul/bannerimage/index");
                return $resultRedirect;
            }
        }
        $resultPage = $this->resultPageFactory->create();
        $this->prepareDefaultBannerTitle($resultPage);
        $resultPage->setActiveMenu("Webkul_MobikulCore::bannerimage");
        if ($isExistingBanner) {
            $resultPage->getConfig()->getTitle()->prepend(__("Edit Item with id %1", $bannerimageId));
        } else {
            $resultPage->getConfig()->getTitle()->prepend(__("New Banner"));
        }
        return $resultPage;
    }

    /**
     * Function to Initialize current Banner
     *
     * @return Int
     */
    protected function initCurrentBanner()
    {
        $bannerimageId = (int)$this->getRequest()->getParam("id");
        if ($bannerimageId) {
            $this->coreRegistry->register(RegistryConstants::CURRENT_BANNER_ID, $bannerimageId);
        }
        return $bannerimageId;
    }

    /**
     * Function to Prepare default banner title
     *
     * @param \Magento\Backend\Model\View\Result\Page $resultPage resultPage
     *
     * @return void
     */
    protected function prepareDefaultBannerTitle(\Magento\Backend\Model\View\Result\Page $resultPage)
    {
        $resultPage->getConfig()->getTitle()->prepend(__("Banner Image"));
    }
}
