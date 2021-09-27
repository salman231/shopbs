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
namespace Webkul\DeliveryBoy\Controller\Adminhtml\Deliveryboy;

class Validate extends \Webkul\DeliveryBoy\Controller\Adminhtml\Deliveryboy
{
    /**
     * @param \Magento\Framework\DataObject $response
     * @return \Webkul\DeliveryBoy\Api\Data\DeliveryboyInterface
     */
    protected function _validateDeliveryboy($response)
    {
        $deliveryboy = null;
        $errors = [];
        try {
            $deliveryboy = $this->deliveryboyDataFactory->create();
            $data = $this->getRequest()->getParams();
            $dataResult = $data["expressdelivery_deliveryboy"];
            $errors = [];
            if (!isset($dataResult["image"][0]["name"])) {
                $errors[] = __("Please upload deliveryboy avatar image.");
            }
            if (!isset($dataResult["name"])) {
                $errors[] = __("Delivery boy name is a required field.");
            }
            if (isset($dataResult["mobile_number"])) {
                if (!is_numeric($dataResult["mobile_number"])) {
                    $errors[] = __("Mobile number should be a valid digits.");
                }
            } else {
                $errors[] = __("Mobile number field can not be blank.");
            }
            $basicPasswordError = false;
            if (!isset($dataResult["password"])) {
                $basicPasswordError = true;
                $errors[] = __("Password is a required field.");
            }
            if (!isset($dataResult["confpassword"])) {
                $basicPasswordError = true;
                $errors[] = __("Password confirmation is a required field.");
            }
            if (!$basicPasswordError) {
                if ($dataResult["password"] != $dataResult["confpassword"]) {
                    $errors[] = __("Password and confirm password should be same.");
                }
            }
            $deliveryboyId = $dataResult["id"] ?? 0;
            $emailCheck = $this->collectionFactory->create()
                ->addFieldToFilter("email", $dataResult["email"])
                ->getFirstItem();
            $mobileCheck = $this->collectionFactory->create()
                ->addFieldToFilter("mobile_number", $dataResult["mobile_number"])
                ->getFirstItem();
            $vehicleCheck = $this->collectionFactory->create()
                ->addFieldToFilter("vehicle_number", $dataResult["vehicle_number"])
                ->getFirstItem();
            if ((bool)$emailCheck->getId()
                && $emailCheck->getId() != $deliveryboyId
            ) {
                $errors[] = __("Delivery boy with same email already exist.");
            } elseif ((bool)$mobileCheck->getId()
                && $mobileCheck->getId() != $deliveryboyId
            ) {
                $errors[] = __("Delivery boy with same mobile number already exist.");
            } elseif ((bool)$vehicleCheck->getId()
                && $vehicleCheck->getId() != $deliveryboyId
            ) {
                $errors[] = __("Delivery boy with same vehicle number already exist.");
            }
        } catch (\Magento\Framework\Validator\Exception $exception) {
            $exceptionMsg = $exception->getMessages(\Magento\Framework\Message\MessageInterface::TYPE_ERROR);
            foreach ($exceptionMsg as $error) {
                $errors[] = $error->getText();
            }
        }
        if ($errors) {
            $messages = $response->hasMessages() ? $response->getMessages() : [];
            foreach ($errors as $error) {
                $messages[] = $error;
            }
            $response->setMessages($messages);
            $response->setError(1);
        }
        return $deliveryboy;
    }

    /**
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $response = new \Magento\Framework\DataObject();
        $response->setError(0);
        $deliveryboy = $this->_validateDeliveryboy($response);
        $resultJson = $this->resultJsonFactory->create();
        if ($response->getError()) {
            $response->setError(true);
            $response->setMessages($response->getMessages());
        }
        $resultJson->setData($response);
        return $resultJson;
    }
}
