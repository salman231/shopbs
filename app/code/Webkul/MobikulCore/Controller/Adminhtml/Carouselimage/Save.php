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

namespace Webkul\MobikulCore\Controller\Adminhtml\Carouselimage;

use Webkul\MobikulCore\Controller\RegistryConstants;

/**
 * Class Save for Carouselimage
 */
class Save extends \Webkul\MobikulCore\Controller\Adminhtml\Carouselimage
{
    /**
     * Execute Fucntion for Class Save
     *
     * @return page
     */
    public function execute()
    {
        $returnToEdit = false;
        $originalRequestData = $this->getRequest()->getPostValue();
        $carouselimageId = $originalRequestData["mobikul_carouselimage"]["id"] ?? null;
        if ($originalRequestData) {
            try {
                $carouselimageData = $originalRequestData["mobikul_carouselimage"];
                $imageName = $this->getCarouselimageName($carouselimageData);
                if (strpos($imageName, "mobikul/carouselimages/") !== false) {
                    $carouselimageData["filename"] = $imageName;
                } else {
                    $carouselimageData["filename"] = "mobikul/carouselimages/".$imageName;
                }
                $request = $this->getRequest();
                $isExistingImage = (bool) $carouselimageId;
                $carouselimage = $this->carouselimageDataFactory->create();
                if ($isExistingImage) {
                    $carouselimageData["id"] = $carouselimageId;
                }
                $carouselimage->setData($carouselimageData);
                // Save carousel image //////////////////////////////////////////////
                $carouselimage = $this->carouselimageRepository->save($carouselimage);
                $carouselimageId = $carouselimage->getId();
                $this->_getSession()->unsCarouselimageFormData();
                // Done Saving carouselimage, finish save action ////////////////////
                $this->coreRegistry->register(RegistryConstants::CURRENT_CAROUSELIMAGE_ID, $carouselimageId);
                $this->messageManager->addSuccess(__("You saved the carousel image."));
                $returnToEdit = (bool) $this->getRequest()->getParam("back", false);
            } catch (\Magento\Framework\Validator\Exception $exception) {
                $messages = $exception->getMessages();
                if (empty($messages)) {
                    $messages = $exception->getMessage();
                }
                $this->_addSessionErrorMessages($messages);
                $this->_getSession()->setCarouselimageFormData($originalRequestData);
                $returnToEdit = true;
            } catch (\Exception $exception) {
                $this->messageManager->addException(
                    $exception,
                    __(
                        "Something went wrong while saving the carousel image. %1",
                        $exception->getMessage()
                    )
                );
                $this->_getSession()->setCarouselimageFormData($originalRequestData);
                $returnToEdit = true;
            }
        }
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($returnToEdit) {
            if ($carouselimageId) {
                $resultRedirect->setPath("mobikul/carouselimage/edit", ["id"=>$carouselimageId, "_current"=>true]);
            } else {
                $resultRedirect->setPath("mobikul/carouselimage/new", ["_current"=>true]);
            }
        } else {
            $resultRedirect->setPath("mobikul/carouselimage/index");
        }
        return $resultRedirect;
    }

    /**
     * Function to get carousel Image name
     *
     * @return array
     */
    private function getCarouselimageName($carouselimageData)
    {
        if (isset($carouselimageData["filename"][0]["name"])) {
            return $carouselimageData["filename"] = $carouselimageData["filename"][0]["name"];
        } else {
            throw new \Magento\Framework\Exception\LocalizedException(__("Please upload carousel image."));
        }
    }
}
