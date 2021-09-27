<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MobikulCore
 * @author    Webkul <support@webkul.com>
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html ASL Licence
 * @link      https://store.webkul.com/license.html
 */

namespace Webkul\MobikulCore\Controller\Adminhtml\Featuredcategories;

use Webkul\MobikulCore\Controller\RegistryConstants;
use Magento\Framework\Exception\NoSuchEntityException;
use Webkul\MobikulCore\Api\Data\FeaturedcategoriesInterface;

/**
 * Class Edit controller
 */
class Edit extends \Webkul\MobikulCore\Controller\Adminhtml\Featuredcategories
{
    public function execute()
    {
        $featuredcategoriesId = $this->initCurrentFeaturedcategories();
        $isExistingFeaturedcategories = (bool)$featuredcategoriesId;
        if ($isExistingFeaturedcategories) {
            try {
                $featuredcategoriesDirPath = $this->mediaDirectory->getAbsolutePath("mobikul/featuredcategories");
                if (!$this->fileDriver->isExists($featuredcategoriesDirPath)) {
                    $this->fileDriver->createDirectory($featuredcategoriesDirPath, 0777);
                }
                $baseTmpPath = "";
                $target = $this->storeManager->getStore()->getBaseUrl(
                    \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
                ).$baseTmpPath;
                $featuredcategoriesData = [];
                $featuredcategoriesData["mobikul_featuredcategories"] = [];
                $featuredcategories = null;
                $featuredcategories = $this->featuredcategoriesRepository->getById($featuredcategoriesId);
                $result = $featuredcategories->getData();
                if (count($result)) {
                    $featuredcategoriesData["mobikul_featuredcategories"] = $result;
                    $featuredcategoriesData["mobikul_featuredcategories"]["filename"] = [];
                    $featuredcategoriesData["mobikul_featuredcategories"]["filename"][0] = [];
                    $featuredcategoriesData["mobikul_featuredcategories"]["filename"][0]["name"] = $result["filename"];
                    $featuredcategoriesData["mobikul_featuredcategories"]["filename"][0]["url"] = $target.$result[
                        "filename"
                    ];
                    $filePath = $this->mediaDirectory->getAbsolutePath($baseTmpPath).$result["filename"];
                    if ($this->fileDriver->isExists($filePath)) {
                        $featuredcategoriesData["mobikul_featuredcategories"]["filename"][0]["size"] =
                        $this->fileHelper->getFileSize(
                            $filePath
                        );
                    } else {
                        $featuredcategoriesData["mobikul_featuredcategories"]["filename"][0]["size"] = 0;
                    }
                    $featuredcategoriesData["mobikul_featuredcategories"][
                        FeaturedcategoriesInterface::ID
                    ] = $featuredcategoriesId;
                    $this->coreRegistry->register(
                        "categoryId",
                        $featuredcategoriesData["mobikul_featuredcategories"]["category_id"]
                    );
                    $this->_getSession()->setFeaturedcategoriesFormData($featuredcategoriesData);
                } else {
                    $this->messageManager->addError(__("Requested featuredcategories doesn't exist"));
                    $resultRedirect = $this->resultRedirectFactory->create();
                    $resultRedirect->setPath("mobikul/featuredcategories/index");
                    return $resultRedirect;
                }
            } catch (NoSuchEntityException $e) {
                $this->messageManager->addException(
                    $e,
                    __("Something went wrong while editing the featuredcategories.")
                );
                $resultRedirect = $this->resultRedirectFactory->create();
                $resultRedirect->setPath("mobikul/featuredcategories/index");
                return $resultRedirect;
            }
        }
        $resultPage = $this->resultPageFactory->create();
        $this->prepareDefaultFeaturedcategoriesTitle($resultPage);
        $resultPage->setActiveMenu("Webkul_MobikulCore::featuredcategories");
        if ($isExistingFeaturedcategories) {
            $resultPage->getConfig()->getTitle()->prepend(__("Edit Item with id %1", $featuredcategoriesId));
        } else {
            $resultPage->getConfig()->getTitle()->prepend(__("New Featured Categories"));
        }
        return $resultPage;
    }

    protected function initCurrentFeaturedcategories()
    {
        $featuredcategoriesId = (int)$this->getRequest()->getParam("id");
        if ($featuredcategoriesId) {
            $this->coreRegistry->register(RegistryConstants::CURRENT_FEATUREDCATEGORIES_ID, $featuredcategoriesId);
        }
        return $featuredcategoriesId;
    }

    protected function prepareDefaultFeaturedcategoriesTitle(\Magento\Backend\Model\View\Result\Page $resultPage)
    {
        $resultPage->getConfig()->getTitle()->prepend(__("Featuredcategories"));
    }
}
