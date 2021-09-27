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

namespace Webkul\MobikulCore\Controller\Adminhtml\Carousel;

use Webkul\MobikulCore\Controller\RegistryConstants;

/**
 * Class Save for Carousel
 */
class Save extends \Webkul\MobikulCore\Controller\Adminhtml\Carousel
{
    /**
     * Execute Function for Class Save
     *
     * @return jSon
     */
    public function execute()
    {
        $returnToEdit = false;
        $originalRequestData = $this->getRequest()->getPostValue();
        $carouselId = $originalRequestData["mobikul_carousel"]["id"] ?? null;
        if ($originalRequestData) {
            try {
                $carouselData = $originalRequestData["mobikul_carousel"];
                $imageName = $this->getImageName($carouselData);
                $carouselData["store_id"] = $this->getCarouselStoreId($carouselData);
                $carouselData["filename"] = $imageName;
                $request = $this->getRequest();
                $carousel = $this->carouselDataFactory->create();
                $isExisting = (bool) $carouselId;
                if ($isExisting) {
                    $carouselData["id"] = $carouselId;
                }
                if ($carouselData["type"] == 1) {
                    $carouselData["product_ids"] = "";
                } else {
                    $carouselData["image_ids"] = "";
                }
                $carousel->setData($carouselData);
                // Save carousel ////////////////////////////////////////////////////
                $carousel   = $this->carouselRepository->save($carousel);
                $carouselId = $carousel->getId();
                $this->_getSession()->unsCarouselFormData();
                // Done Saving carousel, finish save action /////////////////////////
                $this->coreRegistry->register(RegistryConstants::CURRENT_CAROUSEL_ID, $carouselId);
                $this->messageManager->addSuccess(__("You saved the carousel."));
                $returnToEdit = (bool) $this->getRequest()->getParam("back", false);
            } catch (\Magento\Framework\Validator\Exception $exception) {
                $messages = $exception->getMessages();
                if (empty($messages)) {
                    $messages = $exception->getMessage();
                }
                $this->_addSessionErrorMessages($messages);
                $this->_getSession()->setCarouselFormData($originalRequestData);
                $returnToEdit = true;
            } catch (\Exception $exception) {
                $this->messageManager->addException(
                    $exception,
                    __("Something went wrong while saving the carousel. %1", $exception->getMessage())
                );
                $this->_getSession()->setCarouselFormData($originalRequestData);
                $returnToEdit = true;
            }
        }
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($returnToEdit) {
            if ($carouselId) {
                $resultRedirect->setPath("mobikul/carousel/edit", ["id"=>$carouselId, "_current"=>true]);
            } else {
                $resultRedirect->setPath("mobikul/carousel/new", ["_current"=>true]);
            }
        } else {
            $resultRedirect->setPath("mobikul/carousel/index");
        }
        return $resultRedirect;
    }

    /**
     * Function to get Image name
     *
     * @return bool
     */
    private function getImageName($carouselData)
    {
        if (isset($carouselData["filename"][0]["name"])) {
            return $carouselData["filename"] = $carouselData["filename"][0]["name"];
        }
    }

    private function getCarouselStoreId($carouselData)
    {
        if (isset($carouselData["store_id"])) {
            return $carouselData["store_id"] = implode(",", $carouselData["store_id"]);
        } else {
            return $carouselData["store_id"] = 0;
        }
    }
}
