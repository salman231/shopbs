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

use Webkul\DeliveryBoy\Controller\RegistryConstants;
use Webkul\DeliveryBoy\Helper\ModuleGlobalConstants;

class Save extends \Webkul\DeliveryBoy\Controller\Adminhtml\Deliveryboy
{
    /**
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $returnToEdit = false;
        $originalRequestData = $this->getRequest()->getPostValue();
        $deliveryboyId = $originalRequestData["expressdelivery_deliveryboy"]["id"] ?? null;
        if ($originalRequestData) {
            try {
                $deliveryboyData = $originalRequestData["expressdelivery_deliveryboy"];
                $isExisting = (bool)$deliveryboyId;
                $emailCheck = $this->collectionFactory->create()
                    ->addFieldToFilter("email", $deliveryboyData["email"])
                    ->getFirstItem();
                $mobileCheck = $this->collectionFactory->create()
                    ->addFieldToFilter("mobile_number", $deliveryboyData["mobile_number"])
                    ->getFirstItem();
                $vehicleCheck = $this->collectionFactory->create()
                    ->addFieldToFilter("vehicle_number", $deliveryboyData["vehicle_number"])
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
                $imageName = $this->getDeliveryboyImageName($deliveryboyData);
                if (strpos($imageName, "deliveryboy/images/") !== false) {
                    $deliveryboyData["image"] = $imageName;
                } else {
                    $deliveryboyData["image"] = "deliveryboy/images/" . $imageName;
                }
                $deliveryboy = $this->deliveryboyDataFactory->create();
                if ($deliveryboyData["password"] != "" && $deliveryboyData["confpassword"] != "") {
                    if ($deliveryboyData["password"] == $deliveryboyData["confpassword"]) {
                        $deliveryboyData["password"] = $this->operationHelper->getMd5Hash($deliveryboyData["password"]);
                    } else {
                        unset($deliveryboyData["password"]);
                    }
                } else {
                    unset($deliveryboyData["password"]);
                }
                $deliveryboyData["updated_at"] = $this->date->gmtDate();
                if ($isExisting) {
                    $deliveryboyData["id"] = $deliveryboyId;
                } else {
                    $deliveryboyData["created_at"] = $this->date->gmtDate();
                }
                $deliveryboy->setData($deliveryboyData);
                // Save Deliveryboy ///////////////////////////////////////////////////////////////
                if ($isExisting) {
                    $this->deliveryboyRepository->save($deliveryboy);
                } else {
                    $deliveryboy = $this->deliveryboyRepository->save($deliveryboy);
                    $deliveryboy->setPassword($deliveryboyData["confpassword"]);
                    $this->sendEmailToDeliveryboy(
                        $deliveryboy,
                        $this->storeManager->getStore()->getId(),
                        $this->getSenderInfo()
                    );
                    $deliveryboyId = $deliveryboy->getId();
                }
                $this->_getSession()->unsDeliveryboyFormData();
                // Done Saving Deliveryboy, finish save action ////////////////////////////////////
                $this->coreRegistry->register(RegistryConstants::CURRENT_DELIVERYBOY_ID, $deliveryboyId);

                $this->messageManager->addSuccess(__("You saved the deliveryboy."));
                $returnToEdit = (bool) $this->getRequest()->getParam("back", false);
            } catch (\Magento\Framework\Validator\Exception $exception) {
                $messages = $exception->getMessages();
                if (empty($messages)) {
                    $messages = $exception->getMessage();
                }
                $this->_addSessionErrorMessages($messages);
                $this->_getSession()->setDeliveryboyFormData($originalRequestData);
                $returnToEdit = true;
            } catch (\Exception $exception) {
                $this->messageManager->addException(
                    $exception,
                    __("Something went wrong while saving the deliveryboy. %1", $exception->getMessage())
                );
                $this->_getSession()->setDeliveryboyFormData($originalRequestData);
                $returnToEdit = true;
            }
        }
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($returnToEdit) {
            if ($deliveryboyId) {
                $resultRedirect->setPath(
                    "expressdelivery/deliveryboy/edit",
                    ["id" => $deliveryboyId, "_current"=>true]
                );
            } else {
                $resultRedirect->setPath("expressdelivery/deliveryboy/new", ["_current" => true]);
            }
        } else {
            $resultRedirect->setPath("expressdelivery/deliveryboy/index");
        }
        return $resultRedirect;
    }

    /**
     * @param array $deliveryboyData
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getDeliveryboyImageName($deliveryboyData)
    {
        if (isset($deliveryboyData["image"][0]["name"])) {
            return $deliveryboyData["image"] = $deliveryboyData["image"][0]["name"];
        } else {
            throw new \Magento\Framework\Exception\LocalizedException(__("Please upload avatar image."));
        }
    }

    /**
     * @param \Webkul\DeliveryBoy\Model\Deliveryboy $deliveryboy
     * @param int $storeId
     * @param array $senderInfo
     * @return void
     */
    protected function sendEmailToDeliveryboy(
        \Webkul\DeliveryBoy\Model\Deliveryboy $deliveryboy,
        int $storeId,
        array $senderInfo
    ) {
        $this->_eventManager->dispatch(
            'deliveryboy_new_account_after',
            [
                'deliveryboy' => $deliveryboy,
                'store_id' => $storeId,
                'sender_info' => $senderInfo,
            ]
        );
    }

    /**
     * @return array
     */
    protected function getSenderInfo(): array
    {
        $senderInfo = [
            "name"  => ModuleGlobalConstants::DEFAULT_ADMIN_NAME,
            "email" => $this->deliveryboyHelper->getConfigData(
                ModuleGlobalConstants::DEFAULT_ADMIN_EMAIL_XML_PATH
            )
        ];

        return $senderInfo;
    }
}
