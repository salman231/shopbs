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
namespace Webkul\DeliveryBoy\Controller\Api\Admin;

use Magento\Framework\Exception\LocalizedException;

class DeleteReview extends \Webkul\DeliveryBoy\Controller\Api\AbstractRating
{
    /**
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        try {
            $this->verifyRequest();
            $environment = $this->emulate->startEnvironmentEmulation($this->storeId);
            $this->verifyUsernData();
            if (!$this->ratingFactory->create()->load($this->ratingId)->getId()) {
                throw new  LocalizedException((__('Review not found')));
            }
            $this->ratingRepository->deleteById($this->ratingId);
            $this->returnArray["success"] = true;
            $this->returnArray["message"] = __("Review deleted successfully.");
            $this->emulate->stopEnvironmentEmulation($environment);
        } catch (\Throwable $e) {
            $this->returnArray["message"] = __($e->getMessage());
        }

        return $this->getJsonResponse($this->returnArray);
    }

    /**
     * @return void
     * @throws LocalizedException
     */
    protected function verifyUsernData()
    {
        if ($this->adminCustomerEmail !== $this->deliveryboyHelper->getAdminEmail()) {
            throw new LocalizedException(__("Unauthorized access."));
        }
    }

    /**
     * @return void
     * @throws LocalizedException
     */
    public function verifyRequest()
    {
        if ($this->getRequest()->getMethod() == "DELETE" && $this->wholeData) {
            $this->storeId = trim($this->wholeData["storeId"] ?? 1);
            $this->ratingId = trim($this->wholeData["ratingId"] ?? 0);
            $this->adminCustomerEmail = trim($this->wholeData["adminCustomerEmail"] ?? "");
        } else {
            throw new LocalizedException(__("Invalid Request"));
        }
    }
}
