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
 * Class MassDisable for Carouselimage
 */
class MassDisable extends \Webkul\MobikulCore\Controller\Adminhtml\Carouselimage
{
    /**
     * Execute Fucntion for Class MassDisable
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
            $currentCarouselImage = $this->carouselimageRepository->getById($carouselimageId);
            $carouselimageData = $currentCarouselImage->getData();
            if (count($carouselimageData)) {
                $condition = "`id`=".$carouselimageId;
                array_push($coditionArr, $condition);
                $carouselimagesUpdated++;
            }
        }
        $coditionData = implode(" OR ", $coditionArr);
        $collection->setCarouselimageData($coditionData, ["status" => 0]);
        if ($carouselimagesUpdated) {
            $this->messageManager->addSuccess(__("A total of %1 record(s) were disabled.", $carouselimagesUpdated));
        }
        return $resultRedirect->setPath("mobikul/carouselimage/index");
    }

    /**
     * Function to check if the controller isallowed
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed("Webkul_MobikulCore::carouselimage");
    }
}
