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

namespace Webkul\MobikulCore\Controller\Adminhtml\Bannerimage;

/**
 * Class MassEnable controller
 */
class MassEnable extends \Webkul\MobikulCore\Controller\Adminhtml\Bannerimage
{
    /**
     * Execure function for mass delete class
     *
     * @return page
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $bannersUpdated = 0;
        $coditionArr = [];
        foreach ($collection->getAllIds() as $key => $bannerimageId) {
            $currentBanner = $this->bannerimageRepository->getById($bannerimageId);
            $bannerimageData = $currentBanner->getData();
            if (count($bannerimageData)) {
                $condition = "`id`=".$bannerimageId;
                array_push($coditionArr, $condition);
                $bannersUpdated++;
            }
        }
        $coditionData = implode(" OR ", $coditionArr);
        $collection->setBannerimageData($coditionData, ["status"=>1]);
        if ($bannersUpdated) {
            $this->messageManager->addSuccess(__("A total of %1 record(s) were enabled.", $bannersUpdated));
        }
        return $resultRedirect->setPath("mobikul/bannerimage/index");
    }

    /**
     * Function to check is class is allowed to be accessed
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed("Webkul_MobikulCore::bannerimage");
    }
}
