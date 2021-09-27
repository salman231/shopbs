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

namespace Webkul\MobikulApi\Controller\Catalog;

/**
 * Class Product Share
 * Share products with other customer by email
 *
 */
class ProductShare extends AbstractCatalog
{
    /**
     * Execute function for class ProductShare
     */
    public function execute()
    {
        try {
            $this->verifyRequest();
            $environment = $this->emulate->startEnvironmentEmulation($this->storeId);
            $senderData = [
                "name" => $this->customerName,
                "email" => $this->customerEmail,
                "message" => $this->message
            ];
            $recipientData = [];
            $recipientData["name"] = $this->recipientName;
            $recipientData["email"] = $this->recipientEmail;
            $product = $this->productFactory->create()->load($this->productId);
            $sendFriend = $this->sendFriend;
            $sendFriend->setSender($senderData);
            $sendFriend->setRecipients($recipientData);
            $sendFriend->setProduct($product);
            $validate = $sendFriend->validate();
            if ($validate === true) {
                $sendFriend->send();
                $this->returnArray["message"] = __("The link to a friend was sent.");
                $this->returnArray["success"] = true;
                $this->emulate->stopEnvironmentEmulation($environment);
                return $this->getJsonResponse($this->returnArray);
            } else {
                if (is_array($validate)) {
                    $this->returnArray["message"] = implode(", ", $validate);
                } else {
                    $this->returnArray["message"] = __("We found some problems with the data.");
                }
            }
            $this->emulate->stopEnvironmentEmulation($environment);
            $this->helper->log($this->returnArray, "logResponse", $this->wholeData);
            return $this->getJsonResponse($this->returnArray);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->returnArray["message"] = __($e->getMessage());
            $this->helper->printLog($this->returnArray);
            return $this->getJsonResponse($this->returnArray);
        } catch (\Exception $e) {
            $this->returnArray["message"] = __("Some emails were not sent.");
            $this->helper->printLog($this->returnArray);
            return $this->getJsonResponse($this->returnArray);
        }
    }

    /**
     * Function verify Request to authenticate the request
     * Authenticates the request and logs the result for invalid requests
     *
     * @return Json
     */
    protected function verifyRequest()
    {
        if ($this->getRequest()->getMethod() == "POST" && $this->wholeData) {
            $this->storeId = $this->wholeData["storeId"] ?? 1;
            $this->message = $this->wholeData["message"] ?? "";
            $this->productId = $this->wholeData["productId"] ?? 0;
            $this->customerName = $this->wholeData["customerName"] ?? "";
            $this->customerEmail = $this->wholeData["customerEmail"] ?? "";
            $this->recipientName = $this->wholeData["recipientName"] ?? "[]";
            $this->recipientEmail = $this->wholeData["recipientEmail"] ?? "[]";
            $this->recipientName = $this->jsonHelper->jsonDecode($this->recipientName);
            $this->recipientEmail = $this->jsonHelper->jsonDecode($this->recipientEmail);
        } else {
            throw new \BadMethodCallException(__("Invalid Request"));
        }
    }
}
