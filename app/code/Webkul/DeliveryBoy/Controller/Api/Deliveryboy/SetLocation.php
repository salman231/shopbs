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

class SetLocation extends \Webkul\DeliveryBoy\Controller\Api\AbstractDeliveryboy
{
    /**
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        try {
            $this->verifyRequest();
            $environment = $this->emulate->startEnvironmentEmulation($this->storeId);
            $deliveryboy = $this->deliveryboy->load($this->deliveryboyId);
            if ($deliveryboy->getStatus() == 1) {
                $deliveryboy->setLatitude($this->latitude)
                    ->setLongitude($this->longitude)
                    ->save();
                $this->returnArray["success"] = true;
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
        if ($this->getRequest()->getMethod() == "POST" && $this->wholeData) {
            $this->storeId = trim($this->wholeData["storeId"] ?? 1);
            $this->latitude = trim($this->wholeData["latitude"] ?? "");
            $this->longitude = trim($this->wholeData["longitude"] ?? "");
            $this->deliveryboyId = trim($this->wholeData["deliveryboyId"] ?? 0);
        } else {
            throw new LocalizedException(__("Invalid Request"));
        }
        if (!is_numeric($this->latitude)) {
            throw new LocalizedException(__("Invalid Latitude."));
        }
        if (!is_numeric($this->longitude)) {
            throw new LocalizedException(__("Invalid Longitude."));
        }
        if (!($this->deliveryboyId > 0 &&
             $this->deliveryboy->load($this->deliveryboyId) != $this->deliveryboyId)
        ) {
            throw new LocalizedException(__("Invalid Deliveryboy."));
        }
    }
}
