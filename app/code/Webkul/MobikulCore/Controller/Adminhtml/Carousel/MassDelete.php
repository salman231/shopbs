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

/**
 * Class MassDelete for Carousel massaction
 */
class MassDelete extends \Webkul\MobikulCore\Controller\Adminhtml\Carousel
{
    /**
     * Execute Function for Class MassDelete
     *
     * @return jSon
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $carouselsDeleted = 0;
        foreach ($collection->getAllIds() as $carouselId) {
            if (!empty($carouselId)) {
                try {
                    $this->carouselRepository->deleteById($carouselId);
                    $carouselsDeleted++;
                } catch (\Exception $exception) {
                    $this->messageManager->addError($exception->getMessage());
                }
            }
        }
        if ($carouselsDeleted) {
            $this->messageManager->addSuccess(__("A total of %1 record(s) were deleted.", $carouselsDeleted));
        }
        return $resultRedirect->setPath("mobikul/carousel/index");
    }

    /**
     * Function to check if the action is allowed
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed("Webkul_MobikulCore::carousel");
    }
}
