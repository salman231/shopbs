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

class GetLocation extends AbstractDeliveryboy
{
    /**
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        try {
            $this->verifyRequest();
            $environment = $this->emulate->startEnvironmentEmulation($this->storeId);
            if ($this->deliveryboy->getStatus() == 1) {
                $this->returnArray["success"] = true;
                $this->returnArray["latitude"] = $this->deliveryboy->getLatitude();
                $this->returnArray["longitude"] = $this->deliveryboy->getLongitude();
            } else {
                throw new LocalizedException(__("Invalid Deliveryboy."));
            }
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
            $this->storeId = trim($this->wholeData["storeId"] ?? 1);
            $this->deliveryboyId = trim($this->wholeData["deliveryboyId"] ?? 0);
        } else {
            throw new LocalizedException(__("Invalid Request"));
        }
        if (!($this->deliveryboyId > 0 &&
            $this->deliveryboy->load($this->deliveryboyId) != $this->deliveryboyId)
        ) {
            throw new LocalizedException(__("Invalid Deliveryboy."));
        }
    }
}
