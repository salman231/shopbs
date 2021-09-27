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

class RegisterDevice extends AbstractMobikul
{
    public function execute()
    {
        try {
            $this->verifyRequest();
            $this->deviceToken->saveToken($this->customerId, $this->token, $this->os);
            return $this->getJsonResponse($this->returnArray);
        } catch (\Exception $e) {
            $this->returnArray["message"] = __($e->getMessage());
            $this->helper->printLog($this->returnArray);
            return $this->getJsonResponse($this->returnArray);
        }
    }

    public function verifyRequest()
    {
        if ($this->getRequest()->getMethod() == "POST" && $this->wholeData) {
            $this->os = $this->wholeData["os"] ?? "ios";
            $this->token = $this->wholeData["token"] ?? "";
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
