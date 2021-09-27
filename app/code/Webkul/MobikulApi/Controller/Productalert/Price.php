<?php
/**
 * Webkul Software.
 *
 *
 *
 * @category  Webkul
 * @package   Webkul_MobikulApi
 * @author    Webkul <support@webkul.com>
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html ASL Licence
 * @link      https://store.webkul.com/license.html
 */

namespace Webkul\MobikulApi\Controller\Productalert;

class Price extends \Webkul\MobikulApi\Controller\ApiController
{
    protected $emulate;
    protected $jsonHelper;
    protected $storeManager;
    protected $productLoader;
    protected $productAlertPrice;

    public function __construct(
        \Webkul\MobikulCore\Helper\Data $helper,
        \Magento\Store\Model\App\Emulation $emulate,
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Webkul\MobikulCore\Helper\Catalog $helperCatalog,
        \Magento\ProductAlert\Model\Price $productAlertPrice,
        \Magento\Catalog\Model\ProductFactory $productLoader,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->emulate = $emulate;
        $this->jsonHelper = $jsonHelper;
        $this->storeManager = $storeManager;
        $this->productLoader = $productLoader;
        $this->productAlertPrice = $productAlertPrice;
        parent::__construct($helper, $context, $jsonHelper);
    }

    public function execute()
    {
        try {
            if ($this->wholeData) {
                $storeId = $this->wholeData["storeId"] ?? 1;
                $productId = $this->wholeData["productId"] ?? 0;
                $customerToken = $this->wholeData["customerToken"] ?? "";
                $customerId = $this->helper->getCustomerByToken($customerToken) ?? 0;
                // Checking customer token //////////////////////////////////////
                if (!$customerId && $customerToken != "") {
                    $returnArray["message"] = __(
                        "As customer you are requesting does not exist, so you need to logout."
                    );
                    $returnArray["otherError"] = "customerNotExist";
                    $customerId = 0;
                }
                // End checking customer token //////////////////////////////////
                $environment = $this->emulate->startEnvironmentEmulation($storeId);
                $product = $this->productLoader->create()->load($productId);
                $websiteId = $this->storeManager->getStore()->getWebsiteId();
                $model = $this->productAlertPrice
                    ->setCustomerId($customerId)
                    ->setProductId($product->getId())
                    ->setPrice($product->getFinalPrice())
                    ->setWebsiteId($websiteId)
                    ->save();
                $returnArray["message"] = __("You saved the alert subscription.");
                $returnArray["success"] = true;
                $this->emulate->stopEnvironmentEmulation($environment);
                return $this->getJsonResponse($returnArray);
            } else {
                throw new \BadMethodCallException(__("Invalid Request"));
            }
        } catch (\NoSuchEntityException $noEntityException) {
            $returnArray["message"] = __("There are not enough parameters.");
            $this->helper->printLog($returnArray);
            return $this->getJsonResponse($returnArray);
        } catch (\Exception $e) {
            $returnArray["message"] = __("We can't update the alert subscription right now.");
            $this->helper->printLog($returnArray);
            return $this->getJsonResponse($returnArray);
        }
    }
}
