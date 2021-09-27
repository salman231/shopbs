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

namespace Webkul\MobikulCore\Controller\Adminhtml\Categoryimages;

use Webkul\MobikulCore\Controller\RegistryConstants;

/**
 * Class Delete for Categoryimages
 */
class Delete extends \Webkul\MobikulCore\Controller\Adminhtml\Categoryimages
{
    /**
     * Execute Function for class Delete
     *
     * @return bool
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $formKeyIsValid = $this->_formKeyValidator->validate($this->getRequest());
        $isPost = $this->getRequest()->isPost();
        if (!$formKeyIsValid || !$isPost) {
            $this->messageManager->addError(__("Category image record could not be deleted."));
            return $resultRedirect->setPath("mobikul/categoryimages/index");
        }
        $categoryimagesId = $this->initCurrentCategoryimages();
        if (!empty($categoryimagesId)) {
            try {
                $this->categoryimagesRepository->deleteById($categoryimagesId);
                $this->messageManager->addSuccess(__("Category image record has been deleted."));
            } catch (\Exception $exception) {
                $this->messageManager->addError($exception->getMessage());
            }
        }
        return $resultRedirect->setPath("mobikul/categoryimages/index");
    }

    /**
     * Function to init the current category Images
     *
     * @return bool
     */
    protected function initCurrentCategoryimages()
    {
        $categoryimagesId = (int)$this->getRequest()->getParam("id");
        if ($categoryimagesId) {
            $this->coreRegistry->register(RegistryConstants::CURRENT_CATEGORYIMAGES_ID, $categoryimagesId);
        }
        return $categoryimagesId;
    }
}
