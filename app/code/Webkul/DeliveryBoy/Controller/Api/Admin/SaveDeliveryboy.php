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
namespace Webkul\DeliveryBoy\Controller\Api\Admin;

use Magento\Framework\Exception\LocalizedException;
use Webkul\DeliveryBoy\Helper\ModuleGlobalConstants;

class SaveDeliveryboy extends \Webkul\DeliveryBoy\Controller\Api\AbstractDeliveryboy
{
    /**
     * @var \Webkul\DeliveryBoy\Api\Data\DeliveryboyInterface
     */
    protected $deliveryboy;

    /**
     * Deliveryboy data in array
     *
     * @var array
     */
    protected $deliveryboyData = [];

    /**
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        try {
            $this->verifyRequest();
            $environment = $this->emulate->startEnvironmentEmulation($this->storeId);
            $this->verifyUsernData();
            $this->verifyImage();
            $this->deliveryboy = $this->deliveryboyDataFactory->create();
            if ($this->password != "" && $this->confpassword != "") {
                if ($this->password == $this->confpassword) {
                    $this->deliveryboyData["password"] = $this->operationHelper->getMd5Hash($this->password);
                } else {
                    unset($this->deliveryboyData["password"]);
                }
            } else {
                unset($this->deliveryboyData["password"]);
            }
            
            if ($this->deliveryboyId != 0) {
                $this->deliveryboyData["id"] = $this->deliveryboyId;
                $this->deliveryboyData["updated_at"] = $this->date->gmtDate();
            } else {
                $this->deliveryboyData["created_at"]
                    = $this->deliveryboyData["updated_at"]
                    = $this->date->gmtDate();
            }
            
            $this->deliveryboyData["name"] = $this->name;
            $this->deliveryboyData["email"] = $this->email;
            if ($this->status == 0 && $this->deliveryboyId != 0) {
                $tableName = $this->resource->getTableName("sales_order_grid");
                $ordersPending = $this->deliveryboyOrderResourceCollection
                    ->create();
                $this->_addBeforeFiltersDeliveryboyOrderResourceCollection($ordersPending);
                $ordersPending->addFieldToFilter("deliveryboy_id", $this->deliveryboyId)
                    ->getSelect()
                    ->join(
                        ["salesOrder" => $tableName],
                        "main_table.order_id = salesOrder.entity_id",
                        ["status" => "status"]
                    );
                $ordersPending->addFieldToFilter(
                    "status",
                    ["in" => ["pending", "processing"]]
                );
                if ($ordersPending->getSize() === 0) {
                    $this->deliveryboyData["status"] = $this->status;
                } else {
                    throw new LocalizedException(__("This Deliveryboy still have pending orders."));
                }
            } else {
                $this->deliveryboyData["status"] = $this->status;
            }
            $this->deliveryboyData["address"] = $this->fullAddress;
            $this->deliveryboyData["vehicle_type"] = $this->vehicleType;
            $this->deliveryboyData["mobile_number"] = $this->mobileNumber;
            $this->deliveryboyData["vehicle_number"] = $this->vehicleNumber;
            $this->deliveryboy->setData($this->deliveryboyData);
            $this->deliveryboyRepository->save($this->deliveryboy);
            $this->returnArray["success"] = true;
            if ((bool)$this->deliveryboyId) {
                $this->returnArray["message"] = __("Deliveryboy details saved successfully.");
            } else {
                $this->returnArray["message"] = __("Deliveryboy created successfully.");
                $this->deliveryboy->setPassword($this->password);
                $this->sendEmailToDeliveryboy(
                    $this->deliveryboy,
                    $this->storeId,
                    $this->getSenderInfo()
                );
            }
            $this->emulate->stopEnvironmentEmulation($environment);
        } catch (\Throwable $e) {
            $this->returnArray["message"] = __($e->getMessage());
        }

        return $this->getJsonResponse($this->returnArray);
    }

    /**
     * @return void
     */
    protected function verifyImage()
    {
        $result = [];
        if (!empty($this->files["image"]["name"])) {
            $target = $this->mediaDirectory->getAbsolutePath("deliveryboy/images");
            try {
                if (!$this->fileDriver->isExists($target)) {
                    $this->fileDriver->createDirectory($target, 0777);
                }
            } catch (\Throwable $e) {
                $this->logger->debug($e-getMessage());
            }
            $uploader = $this->fileUploaderFactory->create(["fileId"=>"image"]);
            $fileName = $this->files["image"]["name"];
            $ext = substr($fileName, strrpos($fileName, ".") + 1);
            $editedFileName = "File-" . time() . "." . $ext;
            $uploader->setAllowedExtensions(["jpg", "jpeg", "gif", "png"]);
            $uploader->setAllowRenameFiles(true);
            $result = $uploader->save($target, $editedFileName);
        }
        if (isset($result["file"])) {
            $this->deliveryboyData["image"] = "deliveryboy/images/" . $result["file"];
        }
    }

    /**
     * @return void
     * @throws LocalizedException
     */
    protected function verifyUsernData()
    {
        if ($this->adminCustomerEmail !== $this->deliveryboyHelper->getAdminEmail()) {
            throw new LocalizedException(__("Unauthorized access."));
        }
    }

    /**
     * @return void
     * @throws LocalizedException
     */
    public function verifyRequest()
    {
        if ($this->getRequest()->getMethod() == "POST" && $this->wholeData) {
            $this->name = trim($this->wholeData["name"] ?? "");
            $this->storeId = trim($this->wholeData["storeId"] ?? 1);
            $this->files = $this->getRequest()->getFiles();
            $this->email = $this->wholeData["email"] ?? "";
            $this->status = isset($this->wholeData["status"])
                ? (int)$this->wholeData["status"]
                : 1;
            $this->password = $this->wholeData["password"] ?? "";
            $this->fullAddress = $this->wholeData["fullAddress"] ?? "";
            $this->vehicleType = $this->wholeData["vehicleType"] ?? "bike";
            $this->confpassword = $this->wholeData["confpassword"] ?? "";
            $this->mobileNumber = ($this->wholeData["mobileNumber"] ?? "");
            $this->deliveryboyId = isset($this->wholeData["deliveryboyId"])
                ? (int)$this->wholeData["deliveryboyId"]
                : 0;
            $this->vehicleNumber = $this->wholeData["vehicleNumber"] ?? "";
            $this->adminCustomerEmail = $this->wholeData["adminCustomerEmail"] ?? "";

            $emailUser = $this->deliveryboyResourceCollection
                ->create()
                ->addFieldToFilter("email", $this->email)
                ->addFieldToFilter("id", ['neq' => $this->deliveryboyId]);
            if ($emailUser->getSize() > 0) {
                throw new LocalizedException(__("Delivery boy with same email already exist."));
            }

            $mobileUser = $this->deliveryboyResourceCollection
                ->create()
                ->addFieldToFilter("mobile_number", $this->mobileNumber)
                ->addFieldToFilter("id", ['neq' => $this->deliveryboyId]);
            if ($mobileUser->getSize() > 0) {
                throw new LocalizedException(__("Delivery boy with same mobile number already exist."));
            }

            $vehicleUser = $this->deliveryboyResourceCollection
                ->create()
                ->addFieldToFilter("vehicle_number", $this->vehicleNumber)
                ->addFieldToFilter("id", ['neq' => $this->deliveryboyId]);
            if ($vehicleUser->getSize() > 0) {
                throw new LocalizedException(__("Delivery boy with same vehicle number already exist."));
            }

        } else {
            throw new LocalizedException(__("Invalid Request"));
        }
    }

    /**
     * @param \Webkul\DeliveryBoy\Model\ResourceModel\Order\Collection $collection
     * @return \Webkul\DeliveryBoy\Model\ResourceModel\Order\Collection
     */
    protected function _addBeforeFiltersDeliveryboyOrderResourceCollection(
        \Webkul\DeliveryBoy\Model\ResourceModel\Order\Collection $collection
    ): \Webkul\DeliveryBoy\Model\ResourceModel\Order\Collection {

        return $collection;
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
