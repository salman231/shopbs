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
 * Class MassEnable for Carousel
 */
class MassEnable extends \Webkul\MobikulCore\Controller\Adminhtml\Carousel
{
    /**
     * Execute Function for Class MassEnable
     *
     * @return jSon
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $carouselsUpdated = 0;
        $coditionArr = [];
        foreach ($collection->getAllIds() as $carouselId) {
            $currentCarousel = $this->carouselRepository->getById($carouselId);
            $carouselData = $currentCarousel->getData();
            if (count($carouselData)) {
                $condition = "`id`=".$carouselId;
                array_push($coditionArr, $condition);
                $carouselsUpdated++;
            }
        }
        $coditionData = implode(" OR ", $coditionArr);
        $collection->setCarouselData($coditionData, ["status"=>1]);
        if ($carouselsUpdated) {
            $this->messageManager->addSuccess(__("A total of %1 record(s) were enabled.", $carouselsUpdated));
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
