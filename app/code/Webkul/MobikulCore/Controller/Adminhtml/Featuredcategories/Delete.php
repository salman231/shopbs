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

use Webkul\MobikulCore\Controller\RegistryConstants;

/**
 * Class Delete controller
 */
class Delete extends \Webkul\MobikulCore\Controller\Adminhtml\Featuredcategories
{
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $formKeyIsValid = $this->_formKeyValidator->validate($this->getRequest());
        $isPost = $this->getRequest()->isPost();
        if (!$formKeyIsValid || !$isPost) {
            $this->messageManager->addError(__("Featured categories could not be deleted."));
            return $resultRedirect->setPath("mobikul/featuredcategories/index");
        }
        $featuredcategoriesId = $this->initCurrentFeaturedcategories();
        if (!empty($featuredcategoriesId)) {
            try {
                $this->featuredcategoriesRepository->deleteById($featuredcategoriesId);
                $this->messageManager->addSuccess(__("Featured categories has been deleted."));
            } catch (\Exception $exception) {
                $this->messageManager->addError($exception->getMessage());
            }
        }
        return $resultRedirect->setPath("mobikul/featuredcategories/index");
    }

    protected function initCurrentFeaturedcategories()
    {
        $featuredcategoriesId = (int)$this->getRequest()->getParam("id");
        if ($featuredcategoriesId) {
            $this->coreRegistry->register(RegistryConstants::CURRENT_FEATUREDCATEGORIES_ID, $featuredcategoriesId);
        }
        return $featuredcategoriesId;
    }
}
