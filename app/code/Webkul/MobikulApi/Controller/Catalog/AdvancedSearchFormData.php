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
 * Class AdvanceSearchFormData
 * To provide related data for advance search
 */
class AdvancedSearchFormData extends AbstractCatalog
{
    /**
     * Execute funciton
     *
     * @return void
     */
    public function execute()
    {
        try {
            $this->verifyRequest();
            $cacheString = "ADVANCEDSEARCHFORMDATA".$this->storeId;
            if ($this->helper->validateRequestForCache($cacheString, $this->eTag)) {
                return $this->getJsonResponse($this->returnArray, 304);
            }
            $environment = $this->emulate->startEnvironmentEmulation($this->storeId);
            $attributes = $this->advancedCatalogSearch->getAttributes();
            foreach ($attributes as $attribute) {
                $each = [];
                $label = $attribute->getStoreLabel();
                $each["label"] = __($label);
                $each["title"] = __($this->helperCatalog->stripTags($label));
                $each["options"] = $attribute->getSource()->getAllOptions(false);
                $each["inputType"] = $this->helperCatalog->getAttributeInputType($attribute);
                $each["attributeCode"] = $attribute->getAttributeCode();
                $each["maxQueryLength"] = $this->helperCatalog->getMaxQueryLength();
                $this->returnArray["fieldList"][] = $each;
            }
            $this->returnArray["success"] = true;
            $this->emulate->stopEnvironmentEmulation($environment);
            $this->checkNGenerateEtag($cacheString);
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
            $this->storeId = $this->wholeData["storeId"] ?? 1;
        } else {
            throw new \BadMethodCallException(__("Invalid Request"));
        }
    }
}
