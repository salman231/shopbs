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

use Magento\Framework\Controller\ResultFactory;

/**
 * Class MassDelete controller
 */
class MassDelete extends \Webkul\MobikulCore\Controller\Adminhtml\Featuredcategories
{
    public function execute()
    {
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $resultRedirect = $this->resultRedirectFactory->create();
        $featuredcategoriessDeleted = 0;
        foreach ($collection->getAllIds() as $featuredcategoriesId) {
            if (!empty($featuredcategoriesId)) {
                try {
                    $this->featuredcategoriesRepository->deleteById($featuredcategoriesId);
                    $featuredcategoriessDeleted++;
                } catch (\Exception $exception) {
                    $this->messageManager->addError($exception->getMessage());
                }
            }
        }
        if ($featuredcategoriessDeleted) {
            $this->messageManager->addSuccess(
                __("A total of %1 Featured category(s) were deleted.", $featuredcategoriessDeleted)
            );
        }
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath("mobikul/featuredcategories/index");
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed("Webkul_MobikulCore::featuredcategories");
    }
}
