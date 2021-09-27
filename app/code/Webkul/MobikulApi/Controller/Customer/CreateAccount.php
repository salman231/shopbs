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

namespace Webkul\MobikulApi\Controller\Customer;

/**
 * Class Create Account
 * To create customer account
 */
class CreateAccount extends AbstractCustomer
{
    public function execute()
    {
        try {
            $this->verifyRequest();
            $environment = $this->emulate->startEnvironmentEmulation($this->storeId);
            $emailValidator = new \Zend\Validator\EmailAddress();
            if (!$emailValidator->isValid($this->email)) {
                $this->returnArray["message"] = __("Invalid email address.");
                return $this->getJsonResponse($this->returnArray);
            }
            $this->customer = $this->customerFactory->create()->setWebsiteId($this->websiteId)
                ->loadByEmail($this->email);
            $this->customerId = $this->customer->getId();
            if ($this->isSocial == 1 && $this->customerId > 0) {
                $confirmationStatus = $this->accountManagement->getConfirmationStatus($this->customerId);
                if ($confirmationStatus ===
                    \Magento\Customer\Api\AccountManagementInterface::ACCOUNT_CONFIRMATION_REQUIRED
                ) {
                    $this->returnArray["message"] = __(
                        "You must confirm your account. Please check your email for the confirmation link"
                    );
                    return $this->getJsonResponse($this->returnArray);
                }
                $this->returnArray["success"] = true;
                $this->returnArray["message"] = __("Your are now Loggedin");
                $this->returnArray["customerName"] = $this->customer->getName();
                $this->returnArray["customerEmail"] = $this->customer->getEmail();
                $this->returnArray["customerId"] = $this->customer->getId();
                $this->returnArray["customerToken"] = $this->helper->getTokenByCustomerDetails(
                    $this->email,
                    $this->password,
                    $this->customerId
                );
                $this->getCustomerImages();
                $this->tokenHelper->saveToken($this->customerId, $this->token, $this->os);
                return $this->getJsonResponse($this->returnArray);
            } else {
                if ($this->customerId > 0) {
                    $this->returnArray["message"] = __("There is already an account with this email address.");
                    return $this->getJsonResponse($this->returnArray);
                }
            }
            $this->customer = $this->customerFactory->create();
            $customerData = [
                "dob" => $this->dob,
                "email" => $this->email,
                "prefix" => $this->prefix,
                "suffix" => $this->suffix,
                "taxvat" => $this->taxvat,
                "gender" => $this->gender,
                "lastname" => $this->lastName,
                "password" => $this->password,
                "firstname" => $this->firstName,
                "website_id" => $this->websiteId,
                "middlename" => $this->middleName,
                "group_id" => $this->helper->getConfigData(\Magento\Customer\Model\GroupManagement::XML_PATH_DEFAULT_ID)
            ];
            $this->getRequest()->setParams($customerData);
            $customerObject = $this->customerExtractor->extract("customer_account_create", $this->_request);
            // Creating Customer ////////////////////////////////////////////////////
            $this->customer = $this->accountManagement->createAccount($customerObject, $this->password, "");
            $this->customerId = $this->customer->getId();
            $this->customer = $this->customerFactory->create()->load($this->customerId);
            // Setting Social Data //////////////////////////////////////////////////
            if ($this->isSocial == 1) {
                $this->doSocialLogin();
            }
            $this->returnArray["customerName"] = $this->customer->getName();
            $this->customer = $this->customerRepositoryInterface->getById($this->customerId);
            $this->mergeQuote();
            $confirmationStatus = $this->accountManagement->getConfirmationStatus($this->customer->getId());
            if ($confirmationStatus ===
                \Magento\Customer\Api\AccountManagementInterface::ACCOUNT_CONFIRMATION_REQUIRED
            ) {
                $this->returnArray["message"] = __(
                    "You must confirm your account. Please check your email for the confirmation link"
                );
                return $this->getJsonResponse($this->returnArray);
            }
            if ($this->customer->getId() && $this->orderId) {
                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                $order = $objectManager->create(\Magento\Sales\Model\Order::class)->load($this->orderId);
                $order->setCustomerId($this->customer->getId());
                $order->setCustomerIsGuest(0);
                $order->save();
            }
            $quote = $this->helper->getCustomerQuote($this->customerId);
            $this->returnArray["success"] = true;
            $this->returnArray["message"] = __("Your Account has been successfully created");
            $this->returnArray["cartCount"] = $quote->getItemsQty() * 1;
            $this->returnArray["customerEmail"] = $this->email;
            $this->returnArray["customerToken"] = $this->helper->getTokenByCustomerDetails(
                $this->email,
                $this->password,
                $this->customerId
            );
            $this->tokenHelper->saveToken($this->customerId, $this->token, $this->os);
            $this->emulate->stopEnvironmentEmulation($environment);
            return $this->getJsonResponse($this->returnArray);
        } catch (\Exception $e) {
            $this->returnArray["message"] = __($e->getMessage());
            $this->helper->printLog($this->returnArray);
            return $this->getJsonResponse($this->returnArray);
        }
    }

    protected function verifyRequest()
    {
        if ($this->getRequest()->getMethod() == "POST" && $this->wholeData) {
            $this->os = $this->wholeData["os"] ?? "ios";
            $this->dob = $this->wholeData["dob"] ?? "";
            $this->dob = $this->localeDate->formatDate($this->dob);
            $this->email = $this->wholeData["email"] ?? "";
            $this->width = $this->wholeData["width"] ?? 1000;
            $this->token = $this->wholeData["token"] ?? "";
            $this->mobile = $this->wholeData["mobile"] ?? "";
            $this->prefix = $this->wholeData["prefix"] ?? "";
            $this->suffix = $this->wholeData["suffix"] ?? "";
            $this->taxvat = $this->wholeData["taxvat"] ?? "";
            $this->gender = $this->wholeData["gender"] ?? "";
            $this->mFactor = $this->wholeData["mFactor"] ?? 1;
            $this->quoteId = $this->wholeData["quoteId"] ?? 0;
            $this->storeId = $this->wholeData["storeId"] ?? 1;
            $this->isSocial = $this->wholeData["isSocial"] ?? 0;
            $this->password = $this->wholeData["password"] ?? "";
            $this->lastName = $this->wholeData["lastName"] ?? "";
            $this->websiteId = $this->wholeData["websiteId"] ?? 0;
            $this->firstName = $this->wholeData["firstName"] ?? "";
            $this->pictureURL = $this->wholeData["pictureURL"] ?? "";
            $this->middleName = $this->wholeData["middleName"] ?? "";
            $this->orderId = $this->wholeData["orderId"] ?? 0;
            $this->mFactor = $this->helper->calcMFactor($this->mFactor);
        } else {
            throw new \BadMethodCallException(__("Invalid Request"));
        }
    }

    protected function getCustomerImages()
    {
        $this->height = $this->helper->getValidDimensions($this->mFactor, 2*($this->width/3));
        $this->width = $this->helper->getValidDimensions($this->mFactor, $this->width);
        $this->profileHeight = $this->profileWidth = $this->helper->getValidDimensions($this->mFactor, 288);
        $collection = $this->userImage->getCollection()->addFieldToFilter("customer_id", $this->customerId);
        if ($collection->getSize() > 0) {
            $time = time();
            foreach ($collection as $value) {
                if ($value->getBanner() != "") {
                    $basePath = $this->baseDir.DS."mobikul".DS."customerpicture".DS.
                        $this->customerId.DS.$value->getBanner();
                    $newUrl = "";
                    if ($this->fileDriver->isFile($basePath)) {
                        $newPath = $this->baseDir.DS."mobikulresized".DS.$this->width."x".
                            $this->height.DS."customerpicture".DS.$this->customerId.DS.$value->getBanner();
                        $this->helperCatalog->resizeNCache($basePath, $newPath, $this->width, $this->height);
                        $newUrl = $this->helper->getUrl("media")."mobikulresized".DS.$this->width."x".
                            $this->height.DS."customerpicture".DS.$this->customerId.DS.$value->getBanner();
                    }
                    $this->returnArray["bannerImage"] = $newUrl."?".$time;
                }
                if ($value->getProfile() != "") {
                    $basePath = $this->baseDir.DS."mobikul".DS."customerpicture".DS.
                        $this->customerId.DS.$value->getProfile();
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

    protected function doSocialLogin()
    {
        $this->userImage
            ->setBanner($this->pictureURL)
            ->setCustomerId($this->customerId)
            ->setIsSocial(1)
            ->save();
        $this->inlineTranslation->suspend();
        try {
            $emailParams = [];
            $emailParams["customerName"] = $this->customer->getName();
            $emailParams["generatedPassword"] = $this->password;
            $sender = [
                "name" => $this->helper->getConfigData("trans_email/ident_support/name"),
                "email" => $this->helper->getConfigData("trans_email/ident_support/email")
            ];
            $receiver = [
                "name" => $this->customer->getName(),
                "email" => $this->email
            ];
            $this->transportBuilder
                ->setTemplateIdentifier("mobikul_social_login_credential_mail")
                ->setTemplateOptions(
                    [
                        "area" => \Magento\Framework\App\Area::AREA_FRONTEND,
                        "store" => $this->storeManager->getStore()->getId()
                    ]
                )
                ->setTemplateVars($emailParams)
                ->setFrom($sender)
                ->addTo($receiver);
            $transport = $this->transportBuilder->getTransport();
            $transport->sendMessage();
            $this->inlineTranslation->resume();
        } catch (\Exception $e) {
            $this->inlineTranslation->resume();
            $this->returnArray["message"] = __($e->getMessage());
            $this->helper->printLog($this->returnArray);
            return $this->getJsonResponse($this->returnArray);
        }
        $this->returnArray["success"] = true;
        $this->returnArray["message"] = __("Your are now Loggedin");
        $this->returnArray["customerName"] = $this->customer->getName();
        $this->returnArray["customerEmail"] = $this->email;
        $this->returnArray["customerToken"] = $this->helper->getTokenByCustomerDetails(
            $this->email,
            $this->password,
            $this->customerId
        );
        return $this->getJsonResponse($this->returnArray);
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
}
