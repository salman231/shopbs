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

namespace Webkul\MobikulCore\Controller\Adminhtml\Featuredcategories;

use Webkul\MobikulCore\Controller\RegistryConstants;

/**
 * Class Save controller
 */
class Save extends \Webkul\MobikulCore\Controller\Adminhtml\Featuredcategories
{
    public function execute()
    {
        $returnToEdit = false;
        $originalRequestData  = $this->getRequest()->getPostValue();
        $featuredcategoriesId = $originalRequestData["mobikul_featuredcategories"]["id"] ?? null;
        if ($originalRequestData) {
            try {
                $featuredcategoriesData = $originalRequestData["mobikul_featuredcategories"];
                $imageName = $this->getFeaturedcategoriesImageName($featuredcategoriesData);
                if (strpos($imageName, "mobikul/featuredcategories/") !== false) {
                    $featuredcategoriesData["filename"] = $imageName;
                } else {
                    $featuredcategoriesData["filename"] = "mobikul/featuredcategories/".$imageName;
                }
                $featuredcategoriesData["store_id"] = $this->getFeaturedcategoriesStoreId($featuredcategoriesData);
                $request = $this->getRequest();
                $isExistingFeaturedcategories = (bool) $featuredcategoriesId;
                $featuredcategories = $this->featuredcategoriesDataFactory->create();
                if ($isExistingFeaturedcategories) {
                    $currentFeaturedcategories = $this->featuredcategoriesRepository->getById($featuredcategoriesId);
                    $featuredcategoriesData["id"] = $featuredcategoriesId;
                }
                $featuredcategoriesData["updated_at"] = $this->date->gmtDate();
                if (!$isExistingFeaturedcategories) {
                    $featuredcategoriesData["created_at"] = $this->date->gmtDate();
                }
                $featuredcategories->setData($featuredcategoriesData);
                // Save featuredcategories //////////////////////////////////////////
                if ($isExistingFeaturedcategories) {
                    $this->featuredcategoriesRepository->save($featuredcategories);
                } else {
                    $featuredcategories = $this->featuredcategoriesRepository->save($featuredcategories);
                    $featuredcategoriesId = $featuredcategories->getId();
                }
                $this->_getSession()->unsFeaturedcategoriesFormData();
                // Done Saving featuredcategories, finish save action ///////////////
                $this->coreRegistry->register(RegistryConstants::CURRENT_FEATUREDCATEGORIES_ID, $featuredcategoriesId);
                $this->messageManager->addSuccess(__("You saved the featured categories."));
                $returnToEdit = (bool)$this->getRequest()->getParam("back", false);
            } catch (\Magento\Framework\Validator\Exception $exception) {
                $messages = $exception->getMessages();
                if (empty($messages)) {
                    $messages = $exception->getMessage();
                }
                $this->_addSessionErrorMessages($messages);
                $this->_getSession()->setFeaturedcategoriesFormData($originalRequestData);
                $returnToEdit = true;
            } catch (\Exception $exception) {
                $this->messageManager->addException(
                    $exception,
                    __(
                        "Something went wrong while saving the featuredcategories. %1",
                        $exception->getMessage()
                    )
                );
                $this->_getSession()->setFeaturedcategoriesFormData($originalRequestData);
                $returnToEdit = true;
            }
        }
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($returnToEdit) {
            if ($featuredcategoriesId) {
                $resultRedirect->setPath(
                    "mobikul/featuredcategories/edit",
                    ["id"=>$featuredcategoriesId, "_current"=>true]
                );
            } else {
                $resultRedirect->setPath("mobikul/featuredcategories/new", ["_current"=>true]);
            }
        } else {
            $resultRedirect->setPath("mobikul/featuredcategories/index");
        }
        return $resultRedirect;
    }

    private function getFeaturedcategoriesImageName($featuredcategoriesData)
    {
        if (isset($featuredcategoriesData["filename"][0]["name"])) {
            if (isset($featuredcategoriesData["filename"][0]["name"])) {
                return $featuredcategoriesData["filename"] = $featuredcategoriesData["filename"][0]["name"];
            } else {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __("Please upload featuredcategories image.")
                );
            }
        } else {
            throw new \Magento\Framework\Exception\LocalizedException(__("Please upload featuredcategories image."));
        }
    }

    private function getFeaturedcategoriesStoreId($featuredcategoriesData)
    {
        if (isset($featuredcategoriesData["store_id"])) {
            return $featuredcategoriesData["store_id"] = implode(",", $featuredcategoriesData["store_id"]);
        } else {
            return $featuredcategoriesData["store_id"] = 0;
        }
    }
}
