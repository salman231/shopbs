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
 * Class Addtowishlist api
 */
class AddToWishlist extends AbstractCatalog
{
    /**
     * Function Execute
     *
     * @return json
     */
    public function execute()
    {
        try {
            $this->verifyRequest();
            $environment = $this->emulate->startEnvironmentEmulation($this->storeId);
            // Loading wishlist of customer /////////////////////////////////////////
            $wishlist = $this->wishlist->create()->loadByCustomerId($this->customerId, true);
            $product  = $this->productFactory->create()->load($this->productId);
            $paramOptionsArray = [];
            $paramOption = [];
            if (isset($this->params["options"])) {
                $productOptions = $this->params["options"];
                $paramOption = $this->getParamOptionArray($productOptions);
                if (count($paramOption) > 0) {
                    $paramOptionsArray["options"] = $paramOption;
                }
            }
            if ($product->getTypeId() == "downloadable") {
                if (isset($this->params["links"])) {
                    $paramOptionsArray["links"] = $this->params["links"];
                }
            } elseif ($product->getTypeId() == "grouped") {
                if (isset($this->params["super_group"])) {
                    $paramOptionsArray["super_group"] = $this->params["super_group"];
                }
            } elseif ($product->getTypeId() == "configurable") {
                if (isset($this->params["super_attribute"])) {
                    $paramOptionsArray["super_attribute"] = $this->params["super_attribute"];
                }
            } elseif ($product->getTypeId() == "bundle") {
                if (isset($this->params["bundle_option"]) && isset($this->params["bundle_option_qty"])) {
                    $paramOptionsArray["bundle_option"] = $this->params["bundle_option"];
                    $paramOptionsArray["bundle_option_qty"] = $this->params["bundle_option_qty"];
                }
            }
            if (count($paramOptionsArray) > 0) {
                $buyRequest = new \Magento\Framework\DataObject($paramOptionsArray);
            } else {
                $buyRequest = new \Magento\Framework\DataObject();
            }
            if (!$product->getId() || !$product->isVisibleInCatalog()) {
                $this->emulate->stopEnvironmentEmulation($environment);
                $this->returnArray["message"] = __("Cannot specify product.");
                return $this->getJsonResponse($this->returnArray);
            }
            $result = $wishlist->addNewItem($product, $buyRequest);
            if (is_string($result)) {
                throw new \Magento\Framework\Exception\LocalizedException(__($result));
            } else {
                $this->returnArray["itemId"] = (int)$result->getId();
            }
            $wishlist->save();
            $this->returnArray["message"] = __("Item added to wishlist Successfully.");
            $this->eventManager->dispatch(
                "wishlist_add_product",
                ["wishlist"=>$wishlist, "product"=>$product, "item"=>$result]
            );
            $this->emulate->stopEnvironmentEmulation($environment);
            $this->returnArray["success"] = true;
            return $this->getJsonResponse($this->returnArray);
        } catch (\Exception $e) {
            $this->returnArray["message"] = __($e->getMessage());
            $this->helper->printLog($this->returnArray);
            return $this->getJsonResponse($this->returnArray);
        }
    }

    /**
     * Function gte ParamOptionArray
     *
     * @param array $productOptions productOptions
     *
     * @return array
     */
    public function getParamOptionArray($productOptions)
    {
        $paramOption = [];
        foreach ($productOptions as $optionId => $values) {
            $option = $this->productOption->load($optionId);
            $optionType = $option->getType();
            if (in_array($optionType, ["multiple", "checkbox"])) {
                foreach ($values as $optionValue) {
                    $paramOption[$optionId][] = $optionValue;
                }
            } elseif (in_array($optionType, ["radio", "drop_down", "area", "field"])) {
                $paramOption[$optionId] = $values;
            } elseif ($optionType == "file") {
                // Downloading file //////////////////////////////////////////
                $base64String = $productOptions["optionId"]["encodeImage"];
                $fileName = time().$productOptions["optionId"]["name"];
                $fileType = $productOptions["optionId"]["type"];
                $fileWithPath = $this->baseDir.DS.$fileName;
                $ifp = $this->fileDriver->fileOpen($fileWithPath, "wb");
                $this->fileDriver->fileWrite($ifp, $this->base64Json->unserialize($base64String));
                // Assigning file to option //////////////////////////////////
                $fileOption = [
                    "type" => $fileType,
                    "title" => $fileName,
                    "fullpath" => $fileWithPath,
                    "quote_path" => DS."media".DS.$fileName,
                    "secret_key" => substr(hash("sha256", $this->fileDriver->fileGetContents($fileWithPath)), 0, 20)
                ];
                $filesToDelete[] = $fileWithPath;
                $paramOption[$optionId] = $fileOption;
            } elseif ($optionType == "date") {
                $paramOption[$optionId]["day"] = $values["day"];
                $paramOption[$optionId]["year"] = $values["year"];
                $paramOption[$optionId]["month"] = $values["month"];
            } elseif ($optionType == "date_time") {
                $paramOption[$optionId]["day"] = $values["day"];
                $paramOption[$optionId]["year"] = $values["year"];
                $paramOption[$optionId]["hour"] = $values["hour"];
                $paramOption[$optionId]["month"] = $values["month"];
                $paramOption[$optionId]["minute"] = $values["minute"];
                $paramOption[$optionId]["dayPart"] = $values["day_part"];
            } elseif ($optionType == "time") {
                $paramOption[$optionId]["hour"] = $values["hour"];
                $paramOption[$optionId]["minute"] = $values["minute"];
                $paramOption[$optionId]["dayPart"] = $values["day_part"];
            }
        }
        return $paramOption;
    }

    /**
     * Function verify Request to authenticate the request
     * Authenticates the request and logs the result for invalid requests
     *
     * @return Json
     */
    public function verifyRequest()
    {
        if ($this->getRequest()->getMethod() == "POST" && $this->wholeData) {
            $this->params = $this->wholeData["params"] ?? "[]";
            $this->params = $this->jsonHelper->jsonDecode($this->params);
            $this->storeId = $this->wholeData["storeId"] ?? 1;
            $this->productId = $this->wholeData["productId"] ?? 0;
            $this->customerToken = $this->wholeData["customerToken"] ?? "";
            $this->customerId = $this->helper->getCustomerByToken($this->customerToken) ?? 0;
            if (!$this->customerId && $this->customerToken != "") {
                $this->returnArray["otherError"] = "customerNotExist";
                throw new \Magento\Framework\Exception\LocalizedException(
                    __("Customer you are requesting does not exist.")
                );
            }
        } else {
            throw new \BadMethodCallException(__("Invalid Request"));
        }
    }
}
