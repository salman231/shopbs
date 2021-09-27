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
 * Class Delete for Carousel
 */
class Delete extends \Webkul\MobikulCore\Controller\Adminhtml\Carousel
{
    /**
     * Execute Function
     *
     * @return jSon
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $formKeyIsValid = $this->_formKeyValidator->validate($this->getRequest());
        $isPost = $this->getRequest()->isPost();
        if (!$formKeyIsValid || !$isPost) {
            $this->messageManager->addError(__("Carousel could not be deleted."));
            return $resultRedirect->setPath("mobikul/carousel/index");
        }
        $carouselId = $this->initCurrentCarousel();
        if (!empty($carouselId)) {
            try {
                $this->carouselRepository->deleteById($carouselId);
                $this->messageManager->addSuccess(__("Carousel has been deleted."));
            } catch (\Exception $exception) {
                $this->messageManager->addError($exception->getMessage());
            }
        }
        return $resultRedirect->setPath("mobikul/carousel/index");
    }

    /**
     * Function to iniiate current carousel
     *
     * @return string
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
