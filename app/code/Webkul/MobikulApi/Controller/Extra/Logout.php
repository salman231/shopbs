<?php
/**
 * Webkul Software.
 *
 *
 *
 * @category  Webkul
 * @package   Webkul_MobikulApi
 * @author    Webkul <support@webkul.com>
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html ASL Licence
 * @link      https://store.webkul.com/license.html
 */

namespace Webkul\MobikulApi\Controller\Extra;

class Logout extends AbstractMobikul
{
    public function execute()
    {
        try {
            $this->verifyRequest();
            $collection = $this->deviceTokenFactory
                ->create()
                ->getCollection()
                ->addFieldToFilter("customer_id", $this->customerId)
                ->addFieldToFilter("token", $this->token);
            foreach ($collection as $eachToken) {
                $this->deviceTokenFactory->create()->load($eachToken->getId())->setCustomerId(0)->save();
            }
            $this->customerSession->unsetAll();
            $this->coreSession->unsetAll();
            // merging compare list /////////////////////////////////////////////////
            $this->productCompare->setAllowUsedFlat(false);
            $items = $this->compareCollection->create();
            $items->useProductItem(true)->setStoreId($this->storeId);
            $items->setVisitorId($this->visitor->getId());
            $attributes = $this->catalogConfig->getProductAttributes();
            $items->addAttributeToSelect($attributes)
                ->loadComparableAttributes()
                ->addMinimalPrice()
                ->addTaxPercents()
                ->setVisibility($this->productVisibility->getVisibleInSiteIds());
            foreach ($items as $item) {
                $this->compareItem->purgeVisitorByCustomer($item);
                $this->productCompare->calculate(true);
            }
            $this->returnArray["success"] = true;
            return $this->getJsonResponse($this->returnArray);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->returnArray["message"] = __($e->getMessage());
            return $this->getJsonResponse($this->returnArray);
        } catch (\Exception $e) {
            $this->returnArray["message"] = $e->getMessage();
            $this->helper->printLog($this->returnArray);
            return $this->getJsonResponse($this->returnArray);
        }
    }

    public function verifyRequest()
    {
        if ($this->getRequest()->getMethod() == "POST" && $this->wholeData) {
            $this->token = $this->wholeData["token"] ?? "";
            $this->storeId = $this->wholeData["storeId"] ?? 1;
            $this->customerToken = $this->wholeData["customerToken"] ?? "";
            $this->customerId = $this->helper->getCustomerByToken($this->customerToken) ?? 0;
            /////// Checking customer token /////////////////////////////////////////
            if (!$this->customerId && $this->customerToken != "") {
                $this->returnArray["message"] = __(
                    "Customer you are requesting does not exist, so you need to logout."
                );
                $this->returnArray["otherError"] = "customerNotExist";
                $this->customerId = 0;
            } elseif ($this->customerId != 0) {
                $this->customerSession->setCustomerId($this->customerId);
            }
        } else {
            throw new \BadMethodCallException(__("Invalid Request"));
        }
    }
}
