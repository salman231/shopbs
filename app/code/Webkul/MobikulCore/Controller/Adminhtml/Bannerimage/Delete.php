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

use Webkul\MobikulCore\Controller\RegistryConstants;

/**
 * Class to delete banner Image
 */
class Delete extends \Webkul\MobikulCore\Controller\Adminhtml\Bannerimage
{
    /**
     * Execute function for delete Class
     *
     * @return page
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $formKeyIsValid = $this->_formKeyValidator->validate($this->getRequest());
        $isPost = $this->getRequest()->isPost();
        if (!$formKeyIsValid || !$isPost) {
            $this->messageManager->addError(__("Banner could not be deleted."));
            return $resultRedirect->setPath("mobikul/bannerimage/index");
        }
        $bannerimageId = $this->initCurrentBanner();
        if (!empty($bannerimageId)) {
            try {
                $this->bannerimageRepository->deleteById($bannerimageId);
                $this->messageManager->addSuccess(__("Banner has been deleted."));
            } catch (\Exception $exception) {
                $this->messageManager->addError($exception->getMessage());
            }
        }
        return $resultRedirect->setPath("mobikul/bannerimage/index");
    }

    /**
     * Function to intialise current Banner
     *
     * @return int
     */
    protected function initCurrentBanner()
    {
        $bannerimageId = (int)$this->getRequest()->getParam("id");
        if ($bannerimageId) {
            $this->coreRegistry->register(RegistryConstants::CURRENT_BANNER_ID, $bannerimageId);
        }
        return $bannerimageId;
    }
}
