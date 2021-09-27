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
namespace Webkul\DeliveryBoy\Controller\Api;

use Magento\Framework\Exception\LocalizedException;

class GetOrderComments extends AbstractDeliveryboy
{
    /**
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        try {
            $this->verifyRequest();
            $this->verifyUsernData();
            $environment = $this->emulate->startEnvironmentEmulation($this->storeId);
            $pageSize = $this->deliveryboyHelper->getPageSize();
            $commentList = [];
            $commentCollection = $this->commentCollection->create();
            if ($this->deliveryboyOrderId > 0) {
                $commentCollection->addFieldToFilter("deliveryboy_order_id", $this->deliveryboyOrderId);
            }
            $commentCollection->addFieldToFilter("order_increment_id", $this->incrementId);
            $commentCollection->setOrder("created_at", "DESC");
            $this->returnArray["totalCount"] = $commentCollection->getSize();
            // Applying pagination //////////////////////////////////////////////////
            if ($this->pageNumber >= 1 && $this->returnArray["totalCount"]) {
                $commentCollection->setPageSize($pageSize)->setCurPage($this->pageNumber);
            }
            foreach ($commentCollection as $each) {
                $eachComment = [];
                $eachComment["comment"] = $each->getComment();
                $eachComment["createdAt"] = $this->timezone
                    ->date(new \DateTime($each->getCreatedAt()))->format("M d, Y h:i:s A");
                $eachComment["commentedBy"] = $each->getCommentedBy();
                $eachComment["incrementId"] = $each->getOrderIncrementId();
                $commentList[] = $eachComment;
            }
            $this->returnArray["success"] = true;
            $this->returnArray["commentList"] = $commentList;
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
    public function verifyRequest()
    {
        if ($this->getRequest()->getMethod() == "GET" && $this->wholeData) {
            $this->pageNumber = trim($this->wholeData["pageNumber"] ?? 1);
            $this->storeId = trim($this->wholeData["storeId"] ?? 1);
            $this->incrementId = trim($this->wholeData["incrementId"] ?? "");
            $this->deliveryboyOrderId = trim($this->wholeData["deliveryboyOrderId"] ?? 0);
            $this->isDeliveryboy = trim($this->wholeData['isDeliveryboy'] ?? false);
            $this->adminCustomerEmail = trim($this->wholeData["adminCustomerEmail"] ?? "");
        } else {
            throw new LocalizedException(__("Invalid Request"));
        }
    }

    /**
     * @return void
     * @throws LocalizedException
     */
    protected function verifyUsernData()
    {
        if (!($this->isDeliveryBoy() || $this->isAdmin())) {
            throw new LocalizedException(__("Unauthorized access."));
        }
    }

    /**
     * @return bool
     */
    protected function isDeliveryboy(): bool
    {
        return (bool)$this->isDeliveryboy || true;
    }

    /**
     * @return bool
     */
    protected function isAdmin(): bool
    {
        return $this->adminCustomerEmail === $this->deliveryboyHelper->getAdminEmail();
    }
}
