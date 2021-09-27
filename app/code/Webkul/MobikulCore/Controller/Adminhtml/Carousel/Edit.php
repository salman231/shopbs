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

use Webkul\MobikulCore\Api\Data\CarouselInterface;
use Webkul\MobikulCore\Controller\RegistryConstants;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class Edit for Carousel
 */
class Edit extends \Webkul\MobikulCore\Controller\Adminhtml\Carousel
{
    /**
     * Execute Function for Class Edit
     *
     * @return jSon
     */
    public function execute()
    {
        $carouselId = $this->initCurrentCarousel();
        $isExistingCarousel = (bool)$carouselId;
        if ($isExistingCarousel) {
            try {
                $baseTmpPath = "";
                $target = $this->storeManager->getStore()->getBaseUrl(
                    \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
                ).$baseTmpPath;
                $carouselData = [];
                $carouselData["mobikul_carousel"] = [];
                $carousel = $this->carouselRepository->getById($carouselId);
                $result = $carousel->getData();
                if (count($result)) {
                    $carouselData["mobikul_carousel"] = $result;
                    if ($result["filename"]) {
                        $carouselData["mobikul_carousel"]["filename"] = [];
                        $carouselData["mobikul_carousel"]["filename"][0] = [];
                        $carouselData["mobikul_carousel"]["filename"][0]["name"] = $result["filename"];
                        $carouselData["mobikul_carousel"]["filename"][0]["url"] = $target."mobikul/carousel/".$result[
                            "filename"
                        ];
                        $filePath = $this->mediaDirectory->getAbsolutePath($baseTmpPath).$result["filename"];
                        if ($this->fileDriver->isFile($filePath)) {
                            $carouselData["mobikul_carousel"]["filename"][0]["size"] =
                            $this->fileHelper->getFileSize($filePath);
                        } else {
                            $carouselData["mobikul_carousel"]["filename"][0]["size"] = 0;
                        }
                    }
                    $carouselData["mobikul_carousel"][CarouselInterface::ID] = $carouselId;
                    $this->_getSession()->setCarouselFormData($carouselData);
                } else {
                    $this->messageManager->addError(__("Requested carousel doesn't exist"));
                    $resultRedirect = $this->resultRedirectFactory->create();
                    $resultRedirect->setPath("mobikul/carousel/index");
                    return $resultRedirect;
                }
            } catch (NoSuchEntityException $e) {
                $this->messageManager->addException($e, __("Something went wrong while editing the carousel."));
                $resultRedirect = $this->resultRedirectFactory->create();
                $resultRedirect->setPath("mobikul/carousel/index");
                return $resultRedirect;
            }
        }
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu("Webkul_MobikulCore::carousel");
        if ($isExistingCarousel) {
            $resultPage->getConfig()->getTitle()->prepend(__("Edit Item with id %1", $carouselId));
        } else {
            $resultPage->getConfig()->getTitle()->prepend(__("New Carousel"));
        }
        return $resultPage;
    }

    /**
     * Function to initiate current Carousel
     *
     * @return bool
     */
    protected function initCurrentCarousel()
    {
        $carouselId = (int)$this->getRequest()->getParam("id");
        if ($carouselId) {
            $this->coreRegistry->register(RegistryConstants::CURRENT_CAROUSEL_ID, $carouselId);
        }
        return $carouselId;
    }
}
