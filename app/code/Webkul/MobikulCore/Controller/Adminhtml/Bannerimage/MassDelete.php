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
 * Class Mass delete for delete massaction banner images
 */
class MassDelete extends \Webkul\MobikulCore\Controller\Adminhtml\Bannerimage
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
        $bannersDeleted = 0;
        foreach ($collection->getAllIds() as $bannerimageId) {
            if (!empty($bannerimageId)) {
                try {
                    $this->bannerimageRepository->deleteById($bannerimageId);
                    $bannersDeleted++;
                } catch (\Exception $exception) {
                    $this->messageManager->addError($exception->getMessage());
                }
            }
        }
        if ($bannersDeleted) {
            $this->messageManager->addSuccess(__("A total of %1 record(s) were deleted.", $bannersDeleted));
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
