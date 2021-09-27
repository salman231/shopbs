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

/**
 * Class MassDelete for Carouselimage
 */
class MassDelete extends \Webkul\MobikulCore\Controller\Adminhtml\Carouselimage
{
    /**
     * Execute Fucntion for Class MassDelete
     *
     * @return page
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $carouselimagesDeleted = 0;
        foreach ($collection->getAllIds() as $carouselimageId) {
            if (!empty($carouselimageId)) {
                try {
                    $this->carouselimageRepository->deleteById($carouselimageId);
                    $carouselimagesDeleted++;
                } catch (\Exception $exception) {
                    $this->messageManager->addError($exception->getMessage());
                }
            }
        }
        if ($carouselimagesDeleted) {
            $this->messageManager->addSuccess(__("A total of %1 record(s) were deleted.", $carouselimagesDeleted));
        }
        return $resultRedirect->setPath("mobikul/carouselimage/index");
    }

    /**
     * Fucntion to check if the controller is allowed
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed("Webkul_MobikulCore::carouselimage");
    }
}
