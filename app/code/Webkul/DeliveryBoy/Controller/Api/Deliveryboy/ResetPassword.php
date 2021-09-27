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
namespace Webkul\DeliveryBoy\Controller\Api\Deliveryboy;

use Magento\Framework\Exception\LocalizedException;

class ResetPassword extends \Webkul\DeliveryBoy\Controller\Api\AbstractDeliveryboy
{
    /**
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        try {
            $this->verifyRequest();
            $environment = $this->emulate->startEnvironmentEmulation($this->storeId);
            if (!\Zend_Validate::is($this->email, "EmailAddress")) {
                throw new LocalizedException(__("Invalid Username."));
            }
            $deliveryboy = $this->deliveryboyResourceCollection->create()
                ->addFieldToFilter("email", $this->email)
                ->getFirstItem();
            if (!$deliveryboy->getId()) {
                throw new LocalizedException(__("No deliveryboy is associated with provided username."));
            }
            $remainingTime = time()-strtotime($deliveryboy->getRpTokenCreatedAt());
            if ($deliveryboy->getRpToken() == $this->token && $remainingTime <= 3600) {
                if ($this->password != "" && $this->confpassword != "") {
                    if ($this->password == $this->confpassword) {
                        $deliveryboy->setPassword($this->operationHelper->getMd5Hash($this->password));
                        $deliveryboy->setId($deliveryboy->getId());
                        $deliveryboy->save();
                        $this->returnArray["success"] = true;
                        $this->returnArray["message"] = __("Password changed successfully.");
                    } else {
                        $this->returnArray["message"] = __("Password and confirm password should match.");
                    }
                } else {
                    $this->returnArray["message"] = __("Password and confirm password can not be blank.");
                }
            } else {
                $this->returnArray["message"] = __("Token expired.");
            }
            $this->emulate->stopEnvironmentEmulation($environment);
        } catch (\Throwable $e) {
            $this->returnArray["message"] = __($e->getMessage());
            $this->helper->printLog($this->returnArray);
        }

        return $this->getJsonResponse($this->returnArray);
    }

    /**
     * @return void
     * @throws LocalizedException
     */
    public function verifyRequest()
    {
        if ($this->getRequest()->getMethod() == "PUT" && $this->wholeData) {
            $this->storeId = trim($this->wholeData["storeId"] ?? 1);
            $this->email = trim($this->wholeData["email"] ?? "");
            $this->token = trim($this->wholeData["token"] ?? "");
            $this->password = trim($this->wholeData["password"] ?? "");
            $this->confpassword = trim($this->wholeData["confpassword"] ?? "");
        } else {
            throw new LocalizedException(__("Invalid Request"));
        }
    }
}
