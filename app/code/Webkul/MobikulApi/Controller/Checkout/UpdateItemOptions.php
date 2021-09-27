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

namespace Webkul\MobikulApi\Controller\Checkout;

/**
 * Class UpdateItemOptions
 * To update item options in the cart
 */
class UpdateItemOptions extends AbstractCheckout
{
    public function execute()
    {
        try {
            $this->verifyRequest();
            $environment = $this->emulate->startEnvironmentEmulation($this->storeId);
            $quote = new \Magento\Framework\DataObject();
            if ($this->customerId != 0) {
                $quote = $this->helper->getCustomerQuote($this->customerId);
            }
            if ($this->quoteId != 0) {
                $quote = $this->quoteFactory->create()->setStoreId($this->storeId)->load($this->quoteId);
            }
            $quoteItem = $quote->getItemById($this->itemId);
            if (!$quoteItem) {
                throw new \Magento\Framework\Exception\LocalizedException(__("We can't find the quote item."));
            }
            $product = $this->productFactory->create()->load($this->productId);
            $paramOption = [];
            if (isset($this->params["options"])) {
                $productOptions = $this->params["options"];
                foreach ($productOptions as $optionId => $values) {
                    $option     = $this->productOption->load($optionId);
                    $optionType = $option->getType();
                    if (in_array($optionType, ["multiple", "checkbox"])) {
                        foreach ($values as $optionValue) {
                            $paramOption[$optionId][] = $optionValue;
                        }
                    } elseif (in_array($optionType, ["radio", "drop_down", "area", "field"])) {
                        $paramOption[$optionId] = $values;
                    } elseif ($optionType == "file") {
                        // downloading file /////////////////////////////////////////
                        $base64String = $productOptions[$optionId]["encodeImage"];
                        $fileName = time().$productOptions[$optionId]["name"];
                        $fileType = $productOptions[$optionId]["type"];
                        $fileWithPath = $this->helperCatalog->getBasePath().DS.$fileName;
                        $ifp = $this->fileDriver->fileOpen($fileWithPath, "wb");
                        $this->fileDriver->fileWrite($ifp, $this->base64Json->unserialize($base64String));
                        // assigning file to option /////////////////////////////////
                        $fileOption = [
                            "type" => $fileType,
                            "title" => $fileName,
                            "fullpath" => $fileWithPath,
                            "quote_path" => DS."media".DS.$fileName,
                            "secret_key" => substr(
                                hash("sha256", $this->fileDriver->fileGetContents($fileWithPath)),
                                0,
                                20
                            )
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
                        $paramOption[$optionId]["dayPart"] = $values["dayPart"];
                    } elseif ($optionType == "time") {
                        $paramOption[$optionId]["hour"] = $values["hour"];
                        $paramOption[$optionId]["minute"] = $values["minute"];
                        $paramOption[$optionId]["dayPart"] = $values["dayPart"];
                    }
                }
            }
            if (count($this->relatedProducts) == 0) {
                $this->relatedProducts = null;
            }
            $result = $this->setParamOptions($product, $paramOption);
            if ($result == 0) {
                return $this->getJsonResponse($this->returnArray);
            }
            $item = $quote->updateItem($this->itemId, new \Magento\Framework\DataObject($this->params));
            if (is_string($item)) {
                throw new \Magento\Framework\Exception\LocalizedException(__($item));
            }
            if ($item->getHasError()) {
                throw new \Magento\Framework\Exception\LocalizedException(__($item->getMessage()));
            }
            $item->setItemId($this->itemId)->save();
            if (!$quote->getHasError()) {
                $this->returnArray["success"] = true;
                $this->returnArray["message"] = __(
                    "%1 was updated in your shopping cart.",
                    $this->escaper->escapeHtml($item->getProduct()->getName())
                );
            }
            $quote = $this->quoteFactory->create()->setStoreId($this->storeId)->load($quote->getId());
            $this->returnArray["cartCount"] = $quote->getItemsQty() * 1;
            $this->emulate->stopEnvironmentEmulation($environment);
            return $this->getJsonResponse($this->returnArray);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $messages = $e->getMessage();
            $this->returnArray["message"] = $messages;
            if (is_array($messages)) {
                $this->returnArray["message"] = implode(", ", $messages);
            }
            $this->helper->printLog($this->returnArray);
            return $this->getJsonResponse($this->returnArray);
        } catch (\Exception $e) {
            $this->returnArray["message"] = $e->getMessage();
            //  __("We can't update the item right now.");
            $this->helper->printLog($this->returnArray);
            return $this->getJsonResponse($this->returnArray);
        }
    }

    public function setParamOptions($product, $paramOption)
    {
        if ($product->getTypeId() == "downloadable") {
            if (isset($this->params['links'])) {
                $this->params = [
                    "related_product"=>$this->relatedProducts, "links"=>$this->params["links"],
                    "options"=>$paramOption, "qty"=>$this->qty, "product"=>$this->productId
                ];
            } else {
                $this->params = [
                    "related_product"=>$this->relatedProducts, "options"=>$paramOption,
                    "qty"=>$this->qty, "product"=>$this->productId
                ];
            }
        } elseif ($product->getTypeId() == "grouped") {
            if (isset($this->params["super_group"])) {
                $this->params = [
                    "related_product"=>$this->relatedProducts,
                    "super_group"=>$this->params["super_group"],
                    "product"=>$this->productId
                ];
            }
        } elseif ($product->getTypeId() == "configurable") {
            if (isset($this->params["super_attribute"])) {
                $this->params = [
                    "related_product"=>$this->relatedProducts,
                    "super_attribute"=>$this->params["super_attribute"],
                    "options"=>$paramOption, "qty"=>$this->qty, "product_id"=>$this->productId
                ];
            }
        } elseif ($product->getTypeId() == "bundle") {
            if (isset($this->params["bundle_option"]) && isset($this->params["bundle_option_qty"])) {
                $this->coreRegistry->register("product", $product);
                $selectionCollection = $product->getTypeInstance(true)->getSelectionsCollection(
                    $product->getTypeInstance(true)->getOptionsIds($product),
                    $product
                );
                foreach ($selectionCollection as $option) {
                    $selectionQty = $option->getSelectionQty() * 1;
                    $key = $option->getOptionId();
                    if (isset($this->params["bundle_option_qty"][$key])) {
                        $probablyRequestedQty = $this->params["bundle_option_qty"][$key];
                    }
                    if ($selectionQty > 1) {
                        $requestedQty = $selectionQty * $this->qty;
                    } elseif (isset($probablyRequestedQty)) {
                        $requestedQty = $probablyRequestedQty * $this->qty;
                    } else {
                        $requestedQty = 1;
                    }
                    $associateBundleProduct = $this->productFactory->create()->load($option->getProductId());
                    $availableQty = $this->stockRegistry->getStockItem($associateBundleProduct->getId())->getQty();
                    if ($associateBundleProduct->getIsSalable()) {
                        if ($requestedQty > $availableQty) {
                            $this->returnArray["message"] = __("The requested quantity of ").
                                $option->getName().__(" is not available");
                            return 0;
                        }
                    }
                }
                $this->params = [
                    "related_product"=>$this->relatedProducts,
                    "bundle_option"=>$this->params["bundle_option"],
                    "bundle_option_qty"=>$this->params["bundle_option_qty"],
                    "options"=>$paramOption, "qty"=>$this->qty, "product_id"=>$this->productId
                ];
            }
        } else {
            $this->params = [
                "related_product"=>$this->relatedProducts,
                "options"=>$paramOption, "qty"=>$this->qty, "product"=>$this->productId
            ];
        }
        return 1;
    }

    /**
     * Function to verify request
     *
     * @return void|json
     */
    protected function verifyRequest()
    {
        if ($this->getRequest()->getMethod() == "POST" && $this->wholeData) {
            $this->qty = $this->wholeData["qty"] ?? 1;
            $this->itemId = $this->wholeData["itemId"] ?? 0;
            $this->params = $this->wholeData["params"] ?? "{}";
            $this->quoteId = $this->wholeData["quoteId"] ?? 0;
            $this->storeId = $this->wholeData["storeId"] ?? 1;
            $this->productId = $this->wholeData["productId"] ?? 0;
            $this->customerToken = $this->wholeData["customerToken"] ?? "";
            $this->relatedProducts = $this->wholeData["relatedProducts"] ?? "[]";
            $this->customerId = $this->helper->getCustomerByToken($this->customerToken) ?? 0;
            if (!$this->customerId && $this->customerToken != "") {
                $this->returnArray["message"] = __(
                    "As customer you are requesting does not exist, so you need to logout."
                );
                $this->returnArray["otherError"] = "customerNotExist";
                $this->customerId = 0;
            }
            $this->params = $this->jsonHelper->jsonDecode($this->params);
            $this->relatedProducts = $this->jsonHelper->jsonDecode($this->relatedProducts);
        } else {
            throw new \BadMethodCallException(__("Invalid Request"));
        }
    }
}
