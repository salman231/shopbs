<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MobikulCore
 * @author    Webkul <support@webkul.com>
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html ASL Licence
 * @link      https://store.webkul.com/license.html
 */

namespace Webkul\MobikulCore\Controller\Adminhtml\Notification;

/**
 * Validate class to validate notification form
 */
class Validate extends \Webkul\MobikulCore\Controller\Adminhtml\Notification
{
    /**
     * ValidateNotification function
     *
     * @param [type] $response
     * @return \Webkul\MobikulCore\Controller\Adminhtml\Notification $notification
     */
    protected function _validateNotification($response)
    {
        $notification = null;
        $errors = [];
        try {
            $notification = $this->notificationDataFactory->create();
            $data = $this->getRequest()->getParams();
            $dataResult = $data["mobikul_notification"];
            $filterData = "";
            if (isset($dataResult['filter_data'])) {
                $filterData = $this->serializer->unserialize($dataResult["filter_data"] ?? "");
            } else {
                $dataResult['filter_data'] = $dataResult['filter_data'] ?? "";
            }
            $errors = [];
            if (!isset($dataResult["filename"][0]["name"])) {
                $errors[] = __("Please upload notification image.");
            }
            if (isset($dataResult["type"]) && isset($dataResult["pro_cat_id"])) {
                if ($dataResult["type"] == "product") {
                    try {
                        if ($dataResult["pro_cat_id"] != "") {
                            $this->productRepositoryInterface->getById($dataResult["pro_cat_id"]);
                        } else {
                            $errors[] = __("Product Id is required");
                        }
                    } catch (\Exception $exception) {
                        $errors[] = __("Requested product doesn't exist");
                    }
                }
                if ($dataResult["type"] == "category") {
                    try {
                        if ($dataResult["pro_cat_id"] != "") {
                            $this->categoryRepositoryInterface->get($dataResult["pro_cat_id"]);
                        } else {
                            $errors[] = __("Category Id is required");
                        }
                    } catch (\Exception $exception) {
                        $errors[] = __("Requested category doesn't exist");
                    }
                }
            } else {
                $errors[] = __("Notification type or id should be set.");
            }
            if ($dataResult["type"] == "custom") {
                if (!isset($dataResult["collection_type"]) || $dataResult["collection_type"] == "") {
                    $errors[] = __("Please create rule for custom collection.");
                } else {
                    if ($dataResult["collection_type"] == "product_attribute" &&
                        $filterData == "" &&
                        empty($dataResult["attribute"])
                    ) {
                        $errors[] = __("Please select atleast one attribute.");
                    } elseif ($dataResult["collection_type"] == "product_ids" &&
                        $filterData == "" &&
                        empty($dataResult["productIds"])
                    ) {
                        $errors[] = __("Please choose atleast one product.");
                    } elseif ($dataResult["collection_type"] == "product_new" &&
                        $filterData == "" &&
                        (empty($dataResult["productIds"]) || !is_numeric($dataResult["newProductCount"]))
                    ) {
                        $errors[] = __("Please provide valid count.");
                    }
                }
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
        return $notification;
    }

    /**
     * Execute function
     *
     * @return json
     */
    public function execute()
    {
        $response = new \Magento\Framework\DataObject();
        $response->setError(0);
        $notification = $this->_validateNotification($response);
        $resultJson = $this->resultJsonFactory->create();
        if ($response->getError()) {
            $response->setError(true);
            $response->setMessages($response->getMessages());
        }
        $resultJson->setData($response);
        return $resultJson;
    }
}
