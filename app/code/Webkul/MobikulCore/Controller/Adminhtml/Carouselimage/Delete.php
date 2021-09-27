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
 * Class Delete for Carouselimage
 */
class Delete extends \Webkul\MobikulCore\Controller\Adminhtml\Carouselimage
{
    /**
     * Execute Function for Class Delete
     *
     * @return page
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $formKeyIsValid = $this->_formKeyValidator->validate($this->getRequest());
        $isPost = $this->getRequest()->isPost();
        if (!$formKeyIsValid || !$isPost) {
            $this->messageManager->addError(__("Carousel image could not be deleted."));
            return $resultRedirect->setPath("mobikul/carouselimage/index");
        }
        $carouselimageId = $this->initCurrentCarouselimage();
        if (!empty($carouselimageId)) {
            try {
                $this->carouselimageRepository->deleteById($carouselimageId);
                $this->messageManager->addSuccess(__("Carousel image has been deleted."));
            } catch (\Exception $exception) {
                $this->messageManager->addError($exception->getMessage());
            }
        }
        return $resultRedirect->setPath("mobikul/carouselimage/index");
    }

    /**
     * Fucntion to Init current carousel Image
     *
     * @return array
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
