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

use Magento\Framework\Controller\ResultFactory;

/**
 * Class MassDelete for Categoryimages
 */
class MassDelete extends \Webkul\MobikulCore\Controller\Adminhtml\Categoryimages
{
    /**
     * Execute function for class MassDelete
     *
     * @return resultFactory
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $categoryimagessDeleted = 0;
        foreach ($collection->getAllIds() as $categoryimagesId) {
            if (!empty($categoryimagesId)) {
                try {
                    $this->categoryimagesRepository->deleteById($categoryimagesId);
                    $categoryimagessDeleted++;
                } catch (\Exception $exception) {
                    $this->messageManager->addError($exception->getMessage());
                }
            }
        }
        if ($categoryimagessDeleted) {
            $this->messageManager->addSuccess(__("A total of %1 record(s) were deleted.", $categoryimagessDeleted));
        }
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath("mobikul/categoryimages/index");
    }

    /**
     * Function to check if the controller is allowed
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed("Webkul_MobikulCore::categoryimages");
    }
}
