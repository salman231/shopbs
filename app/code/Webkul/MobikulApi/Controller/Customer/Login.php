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

namespace Webkul\MobikulApi\Controller\Customer;

class Login extends AbstractCustomer
{
    public function execute()
    {
        try {
            $this->verifyRequest();
            $environment = $this->emulate->startEnvironmentEmulation($this->storeId);
            $customerModel = $this->customerFactory->create();
            $this->customer = $customerModel->setWebsiteId($this->websiteId)->loadByEmail($this->username);
            if ($this->customer->getId() > 0) {
                $this->customerId = $this->customer->getId();
                $this->customer = $customerModel->setWebsiteId($this->websiteId);
                if ($customerModel->getConfirmation() && $customerModel->isConfirmationRequired()) {
                    $this->returnArray["message"] = __("This account is not confirmed.");
                    return $this->getJsonResponse($this->returnArray);
                }
                $hash = $customerModel->getPasswordHash();
                $validatePassword = false;
                if (!$hash) {
                    $this->returnArray["message"] = __("Invalid login or password.");
                    return $this->getJsonResponse($this->returnArray);
                }
                $validatePassword = $this->encryptor->validateHash($this->password, $hash);
                if (!$validatePassword) {
                    $this->returnArray["message"] = __("Invalid login or password.");
                    return $this->getJsonResponse($this->returnArray);
                }
                $this->returnArray["customerName"] = $this->customer->getName();
                $this->returnArray["customerEmail"] = $this->customer->getEmail();
                $this->returnArray["customerId"] = $this->customer->getId();
                $this->returnArray["customerToken"] = $this->helper->getTokenByCustomerDetails(
                    $this->username,
                    $this->password,
                    $this->customerId
                );
                // Saving Device Token //////////////////////////////////////////////
                $this->tokenHelper->saveToken($this->customer->getId(), $this->token, $this->os);
                $this->height = $this->helper->getValidDimensions($this->mFactor, 2*($this->width/3));
                $this->width = $this->helper->getValidDimensions($this->mFactor, $this->width);
                $this->profileHeight = $this->profileWidth = $this->helper->getValidDimensions($this->mFactor, 288);
                // Getting customer profile and banner image ////////////////////////
                $this->getCustomerImages();
                $this->customer = $this->customerRepositoryInterface->getById($this->customerId);
                // Merging guest quote with customer quote //////////////////////////
                $this->mergeQuote();
                $this->returnArray["cartCount"] = $this->helper->getCartCount(
                    $this->helper->getCustomerQuote($this->customerId)
                );
                // Merging compare list /////////////////////////////////////////////
                $this->mergeProductCompareList();
                $this->returnArray["success"] = true;
            } else {
                $this->returnArray["message"] = __("Invalid login or password.");
            }
            $this->emulate->stopEnvironmentEmulation($environment);
            return $this->getJsonResponse($this->returnArray);
        } catch (\Exception $e) {
            $this->returnArray["message"] = __($e->getMessage());
            $this->helper->printLog($this->returnArray);
            return $this->getJsonResponse($this->returnArray);
        }
    }

    /**
     * Verify Request function to verify Customer and Request
     *
     * @throws Exception customerNotExist
     * @return json | void
     */
    protected function verifyRequest()
    {
        if ($this->getRequest()->getMethod() == "POST" && $this->wholeData) {
            $this->os = $this->wholeData["os"] ?? "ios";
            $this->width = $this->wholeData["width"] ?? 1000;
            $this->token = $this->wholeData["token"] ?? "";
            $this->mobile = $this->wholeData["mobile"] ?? 0;
            $this->storeId = $this->wholeData["storeId"] ?? 0;
            $this->quoteId = $this->wholeData["quoteId"] ?? 0;
            $this->mFactor = $this->wholeData["mFactor"] ?? 1;
            $this->username = $this->wholeData["username"] ?? "";
            $this->password = $this->wholeData["password"] ?? "";
            $this->websiteId = $this->wholeData["websiteId"] ?? 1;
            $this->mFactor = $this->helper->calcMFactor($this->mFactor);
        } else {
            throw new \BadMethodCallException(__("Invalid Request"));
        }
    }

    protected function getCustomerImages()
    {
        $collection = $this->userImage->getCollection()->addFieldToFilter("customer_id", $this->customer->getId());
        if ($collection->getSize() > 0) {
            $time = time();
            foreach ($collection as $value) {
                if ($value->getBanner() != "") {
                    if ($value->getIsSocial() == 1) {
                        $this->returnArray["bannerImage"] = $value->getBanner();
                    } else {
                        $basePath = $this->baseDir.DS."mobikul".DS."customerpicture".DS.$this->customerId.DS.
                        $value->getBanner();
                        $newUrl = "";
                        if ($this->fileDriver->isFile($basePath)) {
                            $newPath = $this->baseDir.DS."mobikulresized".DS.$this->width."x".$this->height.DS.
                            "customerpicture".DS.$this->customerId.DS.$value->getBanner();
                            $this->helperCatalog->resizeNCache($basePath, $newPath, $this->width, $this->height);
                            $newUrl = $this->helper->getUrl("media")."mobikulresized".DS.$this->width."x".$this->height.
                            DS."customerpicture".DS.$this->customerId.DS.$value->getBanner();
                        }
                        $this->returnArray["bannerImage"] = $newUrl."?".$time;
                    }
                }
                if ($value->getProfile() != "") {
                    if ($value->getIsSocial() == 1) {
                        $this->returnArray["profileImage"] = $value->getProfile();
                    } else {
                        $basePath = $this->baseDir.DS."mobikul".DS."customerpicture".DS.$this->customerId.DS.
                        $value->getProfile();
                        $newUrl = "";
                        if ($this->fileDriver->isFile($basePath)) {
                            $newPath = $this->baseDir.DS."mobikulresized".DS.$this->profileWidth."x".
                            $this->profileHeight.DS."customerpicture".DS.$this->customerId.DS.$value->getProfile();
                            $this->helperCatalog->resizeNCache(
                                $basePath,
                                $newPath,
                                $this->profileWidth,
                                $this->profileHeight
                            );
                            $newUrl = $this->helper->getUrl("media")."mobikulresized".DS.$this->profileWidth."x".
                            $this->profileHeight.DS."customerpicture".DS.$this->customerId.DS.$value->getProfile();
                        }
                        $this->returnArray["profileImage"] = $newUrl."?".$time;
                    }
                }
            }
        }
    }

    protected function mergeQuote()
    {
        if ($this->quoteId != 0) {
            $guestQuote = $this->quoteFactory->create()->setStoreId($this->storeId)->load($this->quoteId);
            $customerQuote = $this->helper->getCustomerQuote($this->customerId);
            if ($customerQuote->getId() > 0) {
                $customerQuote->merge($guestQuote)->collectTotals()->save();
            } else {
                $guestQuote->assignCustomer($this->customer)
                    ->setCustomer($this->customer)
                    ->getShippingAddress()
                    ->setCollectShippingRates(true);
                $guestQuote->collectTotals()->save();
            }
        }
    }

    protected function mergeProductCompareList()
    {
        $this->productCompare->setAllowUsedFlat(false);
        $items = $this->productCollectionFactory->create();
        $items->useProductItem(true)->setStoreId($this->storeId);
        $items->setVisitorId($this->visitor->getId());
        $attributes = $this->catalogConfig->getProductAttributes();
        $items->addAttributeToSelect($attributes)
            ->loadComparableAttributes()
            ->setVisibility($this->productVisibility->getVisibleInSiteIds());
        foreach ($items as $item) {
            $item->setCustomerId($this->customerId);
            $this->compareItem->updateCustomerFromVisitor($item);
            $this->productCompare->setCustomerId($this->customerId)->calculate();
        }
    }
}
