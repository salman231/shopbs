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

use Webkul\MobikulCore\Controller\RegistryConstants;
use Magento\Framework\Exception\NoSuchEntityException;
use Webkul\MobikulCore\Api\Data\CategoryimagesInterface;

/**
 * Class Edit for Categoryimages
 */
class Edit extends \Webkul\MobikulCore\Controller\Adminhtml\Categoryimages
{
    /**
     * Execute function for class Edit
     *
     * @return resultFactory
     */
    public function execute()
    {
        $categoryimagesId = $this->initCurrentCategoryimages();
        $isExistingCategoryimages = (bool)$categoryimagesId;
        if ($isExistingCategoryimages) {
            try {
                $categoryIconImageDirPath = $this->mediaDirectory->getAbsolutePath("mobikul/categoryimages/icon");
                $categoryBannerImageDirPath = $this->mediaDirectory->getAbsolutePath("mobikul/categoryimages/banner");
                if (!$this->fileDriver->isExists($categoryIconImageDirPath)) {
                    $this->fileDriver->createDirectory($categoryIconImageDirPath, 0777);
                }
                if (!$this->fileDriver->isExists($categoryBannerImageDirPath)) {
                    $this->fileDriver->createDirectory($categoryBannerImageDirPath, 0777);
                }
                $iconBaseTmpPath = "mobikul/categoryimages/icon/";
                $iconTarget = $this->storeManager->getStore()->getBaseUrl(
                    \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
                ).$iconBaseTmpPath;
                $bannerBaseTmpPath = "mobikul/categoryimages/banner/";
                $bannerTarget = $this->storeManager->getStore()->getBaseUrl(
                    \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
                ).$bannerBaseTmpPath;
                $categoryimagesData = [];
                $categoryimagesData["mobikul_categoryimages"] = [];
                $categoryimages = null;
                $categoryimages = $this->categoryimagesRepository->getById($categoryimagesId);
                $result = $categoryimages->getData();
                if (count($result)) {
                    $categoryimagesData["mobikul_categoryimages"] = $result;
                    $categoryimagesData["mobikul_categoryimages"]["icon"] = [];
                    $categoryimagesData["mobikul_categoryimages"]["icon"][0] = [];
                    $categoryimagesData["mobikul_categoryimages"]["icon"][0]["name"] = $result["icon"];
                    $categoryimagesData["mobikul_categoryimages"]["icon"][0]["url"] = $iconTarget.$result["icon"];
                    $iconFilePath = $this->mediaDirectory->getAbsolutePath($iconBaseTmpPath).$result["icon"];
                    if ($this->fileDriver->isExists($iconFilePath)) {
                        $categoryimagesData["mobikul_categoryimages"]["icon"][0]["size"] =
                        $this->fileHelper->getFileSize($iconFilePath);
                    } else {
                        $categoryimagesData["mobikul_categoryimages"]["icon"][0]["size"] = 0;
                    }
                    $categoryimagesData["mobikul_categoryimages"]["banner"] = [];
                    $categoryimagesData["mobikul_categoryimages"]["banner"][0] = [];
                    $categoryimagesData["mobikul_categoryimages"]["banner"][0]["name"] = $result["banner"];
                    $categoryimagesData["mobikul_categoryimages"]["banner"][0]["url"] = $bannerTarget.$result["banner"];
                    $bannerFilePath = $this->mediaDirectory->getAbsolutePath($bannerBaseTmpPath).$result["banner"];
                    if ($this->fileDriver->isExists($bannerFilePath)) {
                        $categoryimagesData["mobikul_categoryimages"]["banner"][0]["size"] =
                        $this->fileHelper->getFileSize($bannerFilePath);
                    } else {
                        $categoryimagesData["mobikul_categoryimages"]["banner"][0]["size"] = 0;
                    }
                    $categoryimagesData["mobikul_categoryimages"][CategoryimagesInterface::ID] = $categoryimagesId;
                    $this->coreRegistry->register(
                        "categoryId",
                        $categoryimagesData["mobikul_categoryimages"]["category_id"]
                    );
                    $this->_getSession()->setCategoryimagesFormData($categoryimagesData);
                } else {
                    $this->messageManager->addError(__("Requested categoryimages doesn\"t exist"));
                    $resultRedirect = $this->resultRedirectFactory->create();
                    $resultRedirect->setPath("mobikul/categoryimages/index");
                    return $resultRedirect;
                }
            } catch (NoSuchEntityException $e) {
                $this->messageManager->addException($e, __("Something went wrong while editing the category image."));
                $resultRedirect = $this->resultRedirectFactory->create();
                $resultRedirect->setPath("mobikul/categoryimages/index");
                return $resultRedirect;
            }
        }
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu("Webkul_MobikulCore::categoryimages");
        $this->prepareDefaultCategoryimagesTitle($resultPage);
        $resultPage->setActiveMenu("Webkul_MobikulCore::categoryimages");
        if ($isExistingCategoryimages) {
            $resultPage->getConfig()->getTitle()->prepend(__("Edit Item with id %1", $categoryimagesId));
        } else {
            $resultPage->getConfig()->getTitle()->prepend(__("New Category Image"));
        }
        return $resultPage;
    }

    /**
     * Function to init current category images
     *
     * @return int
     */
    protected function initCurrentCategoryimages()
    {
        $categoryimagesId = (int)$this->getRequest()->getParam("id");
        if ($categoryimagesId) {
            $this->coreRegistry->register(RegistryConstants::CURRENT_CATEGORYIMAGES_ID, $categoryimagesId);
        }
        return $categoryimagesId;
    }

    /**
     * Function to prepare default category images title
     *
     * @param \Magento\Backend\Model\View\Result\Page $resultPage resultPage
     *
     * @return void
     */
    protected function prepareDefaultCategoryimagesTitle(\Magento\Backend\Model\View\Result\Page $resultPage)
    {
        $resultPage->getConfig()->getTitle()->prepend(__("Category Image"));
    }
}
