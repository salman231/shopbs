<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MobikulCore
 * @author    Webkul <support@webkul.com>
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html ASL Licence
 * @link      https://store.webkul.com/license.html
 */

namespace Webkul\MobikulCore\Controller\Adminhtml\Featuredcategories;

/**
 * Class MassEnable controller
 */
class MassEnable extends \Webkul\MobikulCore\Controller\Adminhtml\Featuredcategories
{
    /**
     * Execute Function for Class MassEnable
     *
     * @return jSon
     */
    public function execute()
    {
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $coditionArr = [];
        $resultRedirect = $this->resultRedirectFactory->create();
        $featuredcategoriessUpdated = 0;
        foreach ($collection->getAllIds() as $key => $featuredcategoriesId) {
            $currentFeaturedcategories = $this->featuredcategoriesRepository->getById($featuredcategoriesId);
            $featuredcategoriesData    = $currentFeaturedcategories->getData();
            if (count($featuredcategoriesData)) {
                $condition = "`id`=".$featuredcategoriesId;
                array_push($coditionArr, $condition);
                $featuredcategoriessUpdated++;
            }
        }
        $coditionData = implode(" OR ", $coditionArr);
        $collection->setFeaturedcategoriesData($coditionData, ["status"=>1]);
        if ($featuredcategoriessUpdated) {
            $this->messageManager->addSuccess(__("A total of %1 record(s) were enabled.", $featuredcategoriessUpdated));
        }
        return $resultRedirect->setPath("mobikul/featuredcategories/index");
    }

    /**
     * Function to check if the controller is allowed
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed("Webkul_MobikulCore::featuredcategories");
    }
}
