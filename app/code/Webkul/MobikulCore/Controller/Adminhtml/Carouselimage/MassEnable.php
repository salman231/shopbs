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
 * Class MassEnable for Carouselimage
 */
class MassEnable extends \Webkul\MobikulCore\Controller\Adminhtml\Carouselimage
{
    /**
     * Execute Fucntion for Class MassEnable
     *
     * @return page
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $carouselimagesUpdated = 0;
        $coditionArr = [];
        foreach ($collection->getAllIds() as $carouselimageId) {
            $currentCarouselimage = $this->carouselimageRepository->getById($carouselimageId);
            $carouselimageData = $currentCarouselimage->getData();
            if (count($carouselimageData)) {
                $condition = "`id`=".$carouselimageId;
                array_push($coditionArr, $condition);
                $carouselimagesUpdated++;
            }
        }
        $coditionData = implode(" OR ", $coditionArr);
        $collection->setCarouselimageData($coditionData, ["status"=>1]);
        if ($carouselimagesUpdated) {
            $this->messageManager->addSuccess(__("A total of %1 record(s) were enabled.", $carouselimagesUpdated));
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
