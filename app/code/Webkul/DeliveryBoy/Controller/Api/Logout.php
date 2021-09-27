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

class Logout extends AbstractDeliveryboy
{
    /**
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        try {
            $this->verifyRequest();
            $environment = $this->emulate->startEnvironmentEmulation($this->storeId);
            $token = $this->tokenFactory->create()
                ->getCollection()
                ->addFieldToFilter("token", $this->token)
                ->getFirstItem();
            $this->tokenFactory->create()
                ->load($token->getId())
                ->delete();
            $this->returnArray["success"] = true;
            $this->returnArray["message"] = __("Logout Successful.");
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
        if ($this->getRequest()->getMethod() == "DELETE" && $this->wholeData) {
            $this->storeId = trim($this->wholeData["storeId"] ?? 1);
            $this->token = trim($this->wholeData["token"] ?? "");
        } else {
            throw new LocalizedException(__("Invalid Request"));
        }
    }
}
