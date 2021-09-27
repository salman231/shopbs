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

/**
 * Class Save for Categoryimages
 */
class Save extends \Webkul\MobikulCore\Controller\Adminhtml\Categoryimages
{
    /**
     * Execute function for class Save
     *
     * @return resultFactory
     */
    public function execute()
    {
        $returnToEdit = false;
        $originalRequestData = $this->getRequest()->getPostValue();
        $categoryimagesId    = $originalRequestData["mobikul_categoryimages"]["id"] ?? null;
        if ($originalRequestData) {
            try {
                $categoryimagesData = $originalRequestData["mobikul_categoryimages"];
                if (isset($featuredcategoriesData['category_id']) &&
                    ($featuredcategoriesData['category_id'] == '' ||
                    $featuredcategoriesData['category_id'] == 0)
                ) {
                    $resultRedirect = $this->resultRedirectFactory->create();
                    $this->messageManager->addError(
                        __(
                            "Category is required."
                        )
                    );
                    if ($featuredcategoriesId) {
                        $resultRedirect->setPath(
                            "mobikul/featuredcategories/edit",
                            ["id"=>$featuredcategoriesId, "_current"=>true]
                        );
                    } else {
                        $resultRedirect->setPath("mobikul/featuredcategories/new", ["_current"=>true]);
                    }
                    return $resultRedirect;
                }
                $categoryimagesData["icon"] = $this->getCategoryIconImageName($categoryimagesData);
                $categoryimagesData["banner"] = $this->uploadAndSaveBannerImage();
                $categoryData = $this->getCategoryData($categoryimagesData);
                $categoryimagesData["category_name"] = $categoryData->getName();
                $categoryimagesData["store_id"] = $this->getCategoryImageStoreId($categoryimagesData);
                $request = $this->getRequest();
                $isExistingCategoryimages = (bool) $categoryimagesId;
                $categoryimages = $this->categoryimagesDataFactory->create();
                if ($isExistingCategoryimages) {
                    $currentCategoryimages = $this->categoryimagesRepository->getById($categoryimagesId);
                    $categoryimagesData["id"] = $categoryimagesId;
                }
                $categoryimagesData["updated_at"] = $this->date->gmtDate();
                if (!$isExistingCategoryimages) {
                    $categoryimagesData["created_at"] = $this->date->gmtDate();
                }
                $categoryimages->setData($categoryimagesData);
                // Save categoryimages //////////////////////////////////////////////
                if ($isExistingCategoryimages) {
                    $this->categoryimagesRepository->save($categoryimages);
                } else {
                    $categoryimages = $this->categoryimagesRepository->save($categoryimages);
                    $categoryimagesId = $categoryimages->getId();
                }
                $this->_getSession()->unsCategoryimagesFormData();
                // Done Saving categoryimages, finish save action ///////////////////
                $this->coreRegistry->register(RegistryConstants::CURRENT_FEATUREDCATEGORIES_ID, $categoryimagesId);
                $this->messageManager->addSuccess(__("You saved the category image."));
                $returnToEdit = (bool) $this->getRequest()->getParam("back", false);
            } catch (\Magento\Framework\Validator\Exception $exception) {
                $messages = $exception->getMessages();
                if (empty($messages)) {
                    $messages = $exception->getMessage();
                }
                $this->_addSessionErrorMessages($messages);
                $this->_getSession()->setCategoryimagesFormData($originalRequestData);
                $returnToEdit = true;
            } catch (\Exception $exception) {
                $this->messageManager->addException(
                    $exception,
                    __(
                        "Something went wrong while saving the category images. %1",
                        $exception->getMessage()
                    )
                );
                $this->_getSession()->setCategoryimagesFormData($originalRequestData);
                $returnToEdit = true;
            }
        }
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($returnToEdit) {
            if ($categoryimagesId) {
                $resultRedirect->setPath("mobikul/categoryimages/edit", ["id"=>$categoryimagesId, "_current"=>true]);
            } else {
                $resultRedirect->setPath("mobikul/categoryimages/new", ["_current"=>true]);
            }
        } else {
            $resultRedirect->setPath("mobikul/categoryimages/index");
        }
        return $resultRedirect;
    }

    /**
     * Function to get category Image Store Id
     *
     * @param array $categoryimagesData categoryimagesData
     *
     * @return string
     */
    private function getCategoryImageStoreId($categoryimagesData)
    {
        if (isset($categoryimagesData["store_id"])) {
            return $categoryimagesData["store_id"] = implode(",", $categoryimagesData["store_id"]);
        } else {
            return $categoryimagesData["store_id"] = 0;
        }
    }

    /**
     * Function to get category Icon Image Name
     *
     * @param array $categoryimagesData categoryimagesData
     *
     * @return string
     */
    private function getCategoryIconImageName($categoryimagesData)
    {
        if (isset($categoryimagesData["icon"][0]["name"])) {
            if (isset($categoryimagesData["icon"][0]["name"])) {
                return $categoryimagesData["icon"] = $categoryimagesData["icon"][0]["name"];
            } else {
                throw new \Magento\Framework\Exception\LocalizedException(__("Please upload category icon image."));
            }
        } else {
            throw new \Magento\Framework\Exception\LocalizedException(__("Please upload category icon image."));
        }
    }

    /**
     * Function to get category banner Image Name
     *
     * @param array $categoryimagesData categoryimagesData
     *
     * @return string
     */
    private function getCategoryBannerImageName($categoryimagesData)
    {
        if (isset($categoryimagesData["banner"][0]["name"])) {
            if (isset($categoryimagesData["banner"][0]["name"])) {
                return $categoryimagesData["banner"] = $categoryimagesData["banner"][0]["name"];
            } else {
                throw new \Magento\Framework\Exception\LocalizedException(__("Please upload category banner image."));
            }
        } else {
            throw new \Magento\Framework\Exception\LocalizedException(__("Please upload category banner image."));
        }
    }

    /**
     * Function to get category Data
     *
     * @param array $categoryimagesData categoryimagesData
     *
     * @return object
     */
    private function getCategoryData($categoryimagesData)
    {
        if (isset($categoryimagesData["category_id"])) {
            if ($categoryimagesData["category_id"]) {
                try {
                    return $this->categoryRepository->get($categoryimagesData["category_id"]);
                } catch (\Exception $exception) {
                    throw new \Magento\Framework\Exception\LocalizedException(__("Requested category doesn't exist"));
                }
            } else {
                throw new \Magento\Framework\Exception\LocalizedException(__("Category id should be set."));
            }
        } else {
            throw new \Magento\Framework\Exception\LocalizedException(__("Category id should be set."));
        }
    }

    /**
     * Function to save category banner images
     *
     * @return string
     */
    public function uploadAndSaveBannerImage()
    {
        $files = $this->getRequest()->getFiles();
        $exist = $this->getRequest()->getParam("mobikul_categoryimages_exist");
        if (!isset($exist)) {
            $exist = [];
        }
        $storeId = $this->getRequest()->getParam("store_id");
        $path = $this->mediaDirectory->getAbsolutePath("mobikul/categoryimages/banner/");
        $postParams = $this->getRequest()->getParam("mobikul_categoryimages");
        if (isset($postParams["id"]) && $postParams["id"] > 0) {
            $categoryId = $postParams["id"];
        } else {
            $categoryId = null;
        }
        if ($categoryId) {
            $currentCategoryData = $this->categoryimagesRepository->getById($categoryId);
            if ($currentCategoryData->getBanner()) {
                $currentCategoryBannerimages = explode(",", $currentCategoryData->getBanner());
            } else {
                $currentCategoryBannerimages = [];
            }
        } else {
            $currentCategoryBannerimages = [];
        }
        $hasFileToUpload = false;
        if (!empty($files["mobikul_categoryimages_banner"])) {
            $keys = array_keys($files["mobikul_categoryimages_banner"]);
            foreach ($keys as $value) {
                if ($files["mobikul_categoryimages_banner"][$value]["size"] > 0) {
                    $hasFileToUpload = true;
                }
            }
        }
        foreach ($currentCategoryBannerimages as $image) {
            if ($this->file->isExists($path."/".$image) && $hasFileToUpload) {
                if (!in_array($image, $exist)) {
                    $this->file->deleteFile($path."/".$image);
                }
            }
        }
        $finalBannerImages = [];
        foreach ($exist as $file) {
            $finalBannerImages[] = $file;
        }
        if (!empty($files["mobikul_categoryimages_banner"])) {
            $keys = array_keys($files["mobikul_categoryimages_banner"]);
            $time = time();
            foreach ($keys as $value) {
                if ($files["mobikul_categoryimages_banner"][$value]["size"] > 0) {
                    try {
                        $uploader = $this->fileUploaderFactory->create(
                            ["fileId" => "mobikul_categoryimages_banner[".$value."]"]
                        );
                        $fileName = $files["mobikul_categoryimages_banner"][$value]["name"];
                        $ext = substr($fileName, strrpos($fileName, ".") + 1);
                        $editedFileName = "File-".$time.".".$ext;
                        $uploader->setAllowedExtensions(["png", "jpg", "jpeg"]);
                        $uploader->setAllowRenameFiles(true);
                        $result = $uploader->save($path, $editedFileName);
                        $url = $result["file"];
                        $finalBannerImages[] = $url;
                        $time++;
                    } catch (\Exception $e) {
                        throw new \RuntimeException($e->getMessage());
                    }
                }
            }
        }
        if (empty($finalBannerImages)) {
            return "";
        } else {
            $bannerImages = implode(",", $finalBannerImages);
            return $bannerImages;
        }
    }
}
