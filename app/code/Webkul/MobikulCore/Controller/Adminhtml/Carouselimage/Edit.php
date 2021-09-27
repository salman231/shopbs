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
use Magento\Framework\Exception\NoSuchEntityException;
use Webkul\MobikulCore\Api\Data\CarouselimageInterface;

/**
 * Class Edit for Carouselimage
 */
class Edit extends \Webkul\MobikulCore\Controller\Adminhtml\Carouselimage
{
    /**
     * Execute Fucntion for Class Edit
     *
     * @return page
     */
    public function execute()
    {
        $carouselimageId = $this->initCurrentCarouselimage();
        $isExistingCarouselimage = (bool)$carouselimageId;
        if ($isExistingCarouselimage) {
            try {
                $baseTmpPath = "";
                $target = $this->storeManager->getStore()->getBaseUrl(
                    \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
                ).$baseTmpPath;
                $carouselimageData = [];
                $carouselimageData["mobikul_carouselimage"] = [];
                $carouselimage = null;
                $carouselimage = $this->carouselimageRepository->getById($carouselimageId);
                $result = $carouselimage->getData();
                if (count($result)) {
                    $carouselimageData["mobikul_carouselimage"] = $result;
                    $carouselimageData["mobikul_carouselimage"]["filename"] = [];
                    $carouselimageData["mobikul_carouselimage"]["filename"][0] = [];
                    $carouselimageData["mobikul_carouselimage"]["filename"][0]["name"] = $result["filename"];
                    $carouselimageData["mobikul_carouselimage"]["filename"][0]["url"] = $target.$result["filename"];
                    $filePath = $this->mediaDirectory->getAbsolutePath($baseTmpPath).$result["filename"];
                    if ($this->fileDriver->isFile($filePath)) {
                        $carouselimageData["mobikul_carouselimage"]["filename"][0]["size"] =
                        $this->fileHelper->getFileSize($filePath);
                    } else {
                        $carouselimageData["mobikul_carouselimage"]["filename"][0]["size"] = 0;
                    }
                    $carouselimageData["mobikul_carouselimage"][CarouselimageInterface::ID] = $carouselimageId;
                    $this->_getSession()->setCarouselimageFormData($carouselimageData);
                } else {
                    $this->messageManager->addError(__("Requested carousel image doesn't exist"));
                    $resultRedirect = $this->resultRedirectFactory->create();
                    $resultRedirect->setPath("mobikul/carouselimage/index");
                    return $resultRedirect;
                }
            } catch (NoSuchEntityException $e) {
                $this->messageManager->addException($e, __("Something went wrong while editing the carousel image."));
                $resultRedirect = $this->resultRedirectFactory->create();
                $resultRedirect->setPath("mobikul/carouselimage/index");
                return $resultRedirect;
            }
        }
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu("Webkul_MobikulCore::carouselimage");
        if ($isExistingCarouselimage) {
            $resultPage->getConfig()->getTitle()->prepend(__("Edit Item with id %1", $carouselimageId));
        } else {
            $resultPage->getConfig()->getTitle()->prepend(__("New Carousel image"));
        }
        return $resultPage;
    }

    /**
     * Fucntion to init Current Carousel image for Class MassEnable
     *
     * @return int
     */
    protected function initCurrentCarouselimage()
    {
        $carouselimageId = (int)$this->getRequest()->getParam("id");
        if ($carouselimageId) {
            $this->coreRegistry->register(RegistryConstants::CURRENT_CAROUSELIMAGE_ID, $carouselimageId);
        }
        return $carouselimageId;
    }
}
