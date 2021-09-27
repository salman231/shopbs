<?php
/**
 * Webkul Software.
 *
 * PHP version 7.0+
 *
 * @category  Webkul
 * @package   Webkul_DeliveryBoy
 * @author    Webkul <support@webkul.com>
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html ASL Licence
 * @link      https://store.webkul.com/license.html
 */
namespace Webkul\DeliveryBoy\Controller\Adminhtml\Rating;

class MassDelete extends \Webkul\DeliveryBoy\Controller\Adminhtml\Rating
{
    /**
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $ratingsDeleted = 0;
        foreach ($collection->getAllIds() as $ratingId) {
            if (!empty($ratingId)) {
                try {
                    $this->ratingRepository->deleteById($ratingId);
                    $ratingsDeleted++;
                } catch (\Exception $exception) {
                    $this->messageManager->addError($exception->getMessage());
                }
            }
        }
        if ($ratingsDeleted) {
            $this->messageManager->addSuccess(__("A total of %1 record(s) were deleted.", $ratingsDeleted));
        }
        return $resultRedirect->setPath("expressdelivery/rating/index");
    }
}
