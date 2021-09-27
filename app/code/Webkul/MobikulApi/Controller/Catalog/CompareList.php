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
 * Class CompareList api
 */
class CompareList extends AbstractCatalog
{
    /**
     * Execute Function for class CompareList
     */
    public function execute()
    {
        try {
            $this->verifyRequest();
            $currency = $this->wholeData["currency"] ?? $this->store->getBaseCurrencyCode();
            $cacheString = "COMPARELIST".$this->width.$this->storeId.$this->customerToken;
            if ($this->helper->validateRequestForCache($cacheString, $this->eTag)) {
                return $this->getJsonResponse($this->returnArray, 304);
            }
            $environment = $this->emulate->startEnvironmentEmulation($this->storeId);
            // Setting currency /////////////////////////////////////////////////////
            $this->store->setCurrentCurrencyCode($currency);
            // Checking is swatch allowed on colletion page /////////////////////////
            $this->returnArray["showSwatchOnCollection"] = (bool)$this->helper->getConfigData(
                "catalog/frontend/show_swatches_in_product_list"
            );
            // Getting compare list data ////////////////////////////////////////////
            if ($this->items === null) {
                $this->compare->setAllowUsedFlat(false);
                $this->items = $this->compareItemCollectionFactory->create();
                $this->items->useProductItem(true)->setStoreId($this->storeId);
                if ($this->customerId != 0) {
                    $this->items->setCustomerId($this->customerId);
                } else {
                    $this->items->setVisitorId($this->customerVisitor->getId());
                }
                $attributes = $this->catalogConfig->getProductAttributes();
                $this->items
                    ->addAttributeToSelect($attributes)
                    ->loadComparableAttributes()
                    ->setVisibility($this->productVisibility->getVisibleInSiteIds());
            }
            // Getting product list /////////////////////////////////////////////////
            $productList = [];
            $this->items->addMinimalPrice();
            foreach ($this->items as $eachProduct) {
                $product = $this->helperCatalog->getOneProductRelevantData(
                    $eachProduct,
                    $this->storeId,
                    $this->width,
                    $this->customerId
                );
                $productList[] = $product;
            }
            $this->returnArray["productList"] = $productList;
            // Getting attribute value list /////////////////////////////////////////
            $block = $this->compareListBlock;
            $attributeValueList = [];
            foreach ($this->items->getComparableAttributes() as $attribute) {
                $eachRow = [];
                $eachRow["attributeName"] = $this->escaper->escapeHtml(
                    $attribute->getStoreLabel($this->storeId) ? $attribute->getStoreLabel($this->storeId) : __(
                        $attribute->getFrontendLabel()
                    )
                );
                foreach ($this->items as $item) {
                    $eachItem = "";
                    switch ($attribute->getAttributeCode()) {
                        case "price":
                            $eachItem = $this->helperCatalog->stripTags(
                                $this->pricingHelper->currency($item->getFinalPrice())
                            );
                            break;
                        case "small_image":
                            $eachItem = $block->getImage($item, "product_small_image")->toHtml();
                            break;
                        default:
                            $attributeHtml = (string) $block->getProductAttributeValue(
                                $item,
                                $attribute
                            );
                            $value = (is_string($attributeHtml)) ? $attributeHtml : "";
                            $eachItem = $this->catalogHelperOutput->productAttribute(
                                $item,
                                $value,
                                $attribute->getAttributeCode()
                            );
                            break;

                    }
                    $eachRow["value"][] = $eachItem;
                }
                $attributeValueList[] = $eachRow;
            }
            $this->returnArray["attributeValueList"] = $attributeValueList;
            $this->customerSession->setCustomerId(null);
            $this->checkNGenerateEtag($cacheString);
            $this->returnArray["success"] = true;
            return $this->getJsonResponse($this->returnArray);
        } catch (\Exception $e) {
            $this->returnArray["message"] = __($e->getMessage());
            $this->helper->printLog($this->returnArray);
            return $this->getJsonResponse($this->returnArray);
        }
    }

    /**
     * Function verify Request to authenticate the request
     * Authenticates the request and logs the result for invalid requests
     *
     * @return Json
     */
    public function verifyRequest()
    {
        if ($this->getRequest()->getMethod() == "GET" && $this->wholeData) {
            $this->eTag = $this->wholeData["eTag"] ?? "";
            $this->width = $this->wholeData["width"] ?? 1000;
            $this->mFactor = $this->wholeData["mFactor"] ?? 1;
            $this->storeId = $this->wholeData["storeId"] ?? 0;
            $this->customerToken = $this->wholeData["customerToken"] ?? "";
            $this->customerId = $this->helper->getCustomerByToken($this->customerToken) ?? 0;
            if (!$this->customerId && $this->customerToken != "") {
                $this->returnArray["otherError"] = "customerNotExist";
                throw new \Magento\Framework\Exception\LocalizedException(
                    __("Customer you are requesting does not exist.")
                );
            } elseif ($this->customerId != 0) {
                $this->customerSession->setCustomerId($this->customerId);
            }
        } else {
            throw new \BadMethodCallException(__("Invalid Request"));
        }
    }
}
