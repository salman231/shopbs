<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MobikulApi
 * @author    Webkul <support@webkul.com>
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html ASL Licence
 * @link      https://store.webkul.com/license.html
 */

namespace Webkul\MobikulApi\Controller\Customer;

/**
 * Class CheckCustomerByEmail
 * To check by email if customer exists or not
 */
class CheckCustomerByEmail extends AbstractCustomer
{
    public function execute()
    {
        try {
            $this->verifyRequest();
            $cacheString = "CHECKCUSTOMERBYEMAIL".$this->email.$this->storeId;
            if ($this->helper->validateRequestForCache($cacheString, $this->eTag)) {
                return $this->getJsonResponse($this->returnArray, 304);
            }
            $environment = $this->emulate->startEnvironmentEmulation($this->storeId);
            $this->websiteId = $this->storeManager->getStore()->getWebsiteId();
            $this->customer = $this->customerFactory->create()->setWebsiteId($this->websiteId)
                ->loadByEmail($this->email);
            if ($this->customer->getId() > 0) {
                $this->returnArray["isCustomerExist"] = true;
            }
            $this->returnArray["success"] = true;
            $this->emulate->stopEnvironmentEmulation($environment);
            return $this->getJsonResponse($this->returnArray);
        } catch (\Exception $e) {
            $this->returnArray["message"] = __($e->getMessage());
            $this->helper->printLog($this->returnArray);
            return $this->getJsonResponse($this->returnArray);
        }
    }

    /**
     * Verify Request function to verify Customer and Request
     *
     * @throws Exception customerNotExist
     * @return json | void
     */
    protected function verifyRequest()
    {
        if ($this->getRequest()->getMethod() == "GET" && $this->wholeData) {
            $this->eTag = $this->wholeData["eTag"] ?? "";
            $this->email = $this->wholeData["email"] ?? "";
            $this->storeId = $this->wholeData["storeId"] ?? 0;
        } else {
            throw new \BadMethodCallException(__("Invalid Request"));
        }
    }
}
