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

use Webkul\DeliveryBoy\Controller\RegistryConstants;

class Delete extends \Webkul\DeliveryBoy\Controller\Adminhtml\Rating
{
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $formKeyIsValid = $this->_formKeyValidator->validate($this->getRequest());
        if (!$formKeyIsValid) {
            $this->messageManager->addError(__("Rating could not be deleted."));
            return $resultRedirect->setPath("expressdelivery/rating/index");
        }
        $ratingId = $this->initCurrentRating();
        if (!empty($ratingId)) {
            try {
                $this->ratingRepository->deleteById($ratingId);
                $this->messageManager->addSuccess(__("Rating has been deleted."));
            } catch (\Exception $exception) {
                $this->messageManager->addError($exception->getMessage());
            }
        }
        return $resultRedirect->setPath("expressdelivery/rating/index");
    }

    protected function initCurrentRating()
    {
        $ratingId = (int)$this->getRequest()->getParam("id");
        if ($ratingId) {
            $this->coreRegistry->register(RegistryConstants::CURRENT_RATING_ID, $ratingId);
        }
        return $ratingId;
    }
}
