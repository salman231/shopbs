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
 * Class RemoveFromCompare
 * To remove products from comparelist
 */
class RemoveFromCompare extends AbstractCatalog
{
    /**
     * Execute Function for class RemoveFrom Compare
     *
     * @return json
     */
    public function execute()
    {
        try {
            $this->verifyRequest();
            $environment = $this->emulate->startEnvironmentEmulation($this->storeId);
            if ($this->productId != 0) {
                try {
                    $product = $this->productRepository->getById($this->productId, false, $this->storeId);
                } catch (\NoSuchEntityException $e) {
                    $product = null;
                }
                if ($product) {
                    $item = $this->compareItemFactory->create();
                    if ($this->customerId != 0) {
                        $item->setCustomerId($this->customerId);
                    } else {
                        $item->addVisitorId($this->customerVisitor->getId());
                    }
                    $compareitem = $item->loadByProduct($product);
                    if ($compareitem) {
                        $this->listCompareModel->removeProduct($product);
                        $this->returnArray["message"] = htmlspecialchars_decode(
                            __("You removed product %1 from the comparison list.", $product->getName())
                        );
                        $this->eventManager->dispatch("catalog_product_compare_remove_product", ["product" => $item]);
                        $this->compare->calculate();
                    }
                }
            }
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
    protected function verifyRequest()
    {
        if ($this->getRequest()->getMethod() == "DELETE" && $this->wholeData) {
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
