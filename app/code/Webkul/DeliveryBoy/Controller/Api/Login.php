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
namespace Webkul\DeliveryBoy\Controller\Api;

use Magento\Framework\Exception\LocalizedException;

class Login extends AbstractDeliveryboy
{
    /**
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        try {
            $this->verifyRequest();
            $environment = $this->emulate->startEnvironmentEmulation($this->storeId);
            $this->iconHeight = $this->iconWidth = 144 * $this->mFactor;
            if (!\Zend_Validate::is($this->username, "EmailAddress")) {
                throw new LocalizedException(__("Invalid Username."));
            }
            if ($this->password == "") {
                throw new LocalizedException(__("Invalid Password."));
            }
            $this->verifyUser();
            $this->saveToken();
            $this->emulate->stopEnvironmentEmulation($environment);
        } catch (\Throwable $e) {
            $this->returnArray["message"] = __($e->getMessage());
        }

        return $this->getJsonResponse($this->returnArray);
    }

    /**
     * @return void
     * @throws LocalizedException
     */
    protected function verifyUser()
    {
        $this->returnArray["isDeliveryBoy"] = false;
        $customer = $this->customerFactory->create()
            ->setWebsiteId($this->websiteId)->loadByEmail($this->username);
        if ($this->username === $this->deliveryboyHelper->getAdminEmail()) {
            $this->applyCustomer($customer);
            $this->returnArray["isAdmin"] = true;
        } else {
            $this->returnArray["isAdmin"] = false;
            $deliveryboy = $this->deliveryboyResourceCollection
                ->create()
                ->addFieldToFilter("status", 1)
                ->addFieldToFilter("email", $this->username)
                ->getFirstItem();
            if (!$deliveryboy->getId()) {
                throw new LocalizedException(__("No user with this username exist."));
            } else {
                if ($deliveryboy->getPassword() == $this->operationHelper->getMd5Hash($this->password)) {
                    $availableTypes = $this->deliveryboy->getAvailableTypes();
                    $this->returnArray["id"] = $deliveryboy->getId();
                    $this->returnArray["name"] = $deliveryboy->getName();
                    $this->returnArray["email"] = $deliveryboy->getEmail();
                    $this->returnArray["mobile"] = $deliveryboy->getMobileNumber();
                    $this->returnArray["success"] = true;
                    $this->returnArray["vehicleType"] = $availableTypes[$deliveryboy->getVehicleType()];
                    $this->returnArray["onlineStatus"] = (bool)$deliveryboy->getAvailabilityStatus();
                    $this->returnArray["vehicleNumber"] = $deliveryboy->getVehicleNumber();
                    $this->returnArray["isDeliveryBoy"] = true;
                    $newUrl = "";
                    $basePath = $this->baseDir . DIRECTORY_SEPARATOR . $deliveryboy->getImage();
                    try {
                        if ($this->fileDriver->isFile($basePath)) {
                            $newPath = $this->baseDir . DIRECTORY_SEPARATOR . "deliveryboyresized" .
                                DIRECTORY_SEPARATOR.
                                $this->iconWidth . "x" . $this->iconHeight . DIRECTORY_SEPARATOR .
                                $deliveryboy->getImage();
                            $this->helperCatalog->resizeNCache(
                                $basePath,
                                $newPath,
                                $this->iconWidth,
                                $this->iconHeight
                            );
                            $newUrl = $this->deliveryboyHelper->getUrl("media") . "deliveryboyresized" .
                                DIRECTORY_SEPARATOR .
                                $this->iconWidth . "x" . $this->iconHeight . DIRECTORY_SEPARATOR .
                                $deliveryboy->getImage();
                        }
                    } catch (\Throwable $t) {
                        $this->logger->debug($t->getMessage());
                    }
                    $this->returnArray["avatar"] = $newUrl;
                } else {
                    throw new LocalizedException(__("Invalid Password."));
                }
            }
        }
    }

    /**
     * @param \Magento\Customer\Model\Customer $customer
     * @return void
     */
    protected function applyCustomer(\Magento\Customer\Model\Customer $customer)
    {
        $hash = $customer->getPasswordHash();
        $validatePassword = $this->encryptor->validateHash($this->password, $hash);
        if (!$validatePassword) {
            throw new LocalizedException(__("Invalid username or password."));
        } else {
            $this->returnArray["id"] = $customer->getId();
            $this->returnArray["name"] = $customer->getName();
            $this->returnArray["email"] = $customer->getEmail();
            $this->returnArray["success"] = true;
        }
    }

    /**
     * @return void
     * @throws LocalizedException
     */
    public function verifyRequest()
    {
        if ($this->getRequest()->getMethod() == "POST" && $this->wholeData) {
            $this->storeId = trim($this->wholeData["storeId"] ?? 1);
            $this->os = trim($this->wholeData["os"] ?? "");
            $this->token = trim($this->wholeData["token"] ?? "");
            $this->mFactor = trim($this->wholeData["mFactor"] ?? 1);
            $this->username = trim($this->wholeData["username"] ?? "");
            $this->password = trim($this->wholeData["password"] ?? "");
            $this->websiteId = trim($this->wholeData["websiteId"] ?? 1);
        } else {
            throw new LocalizedException(__("Invalid Request"));
        }
    }

    /**
     * @return void
     */
    public function saveToken()
    {
        $tokenCollection = $this->tokenFactory->create()
            ->getCollection()
            ->addFieldToFilter('token', $this->token);
        $deliveryboyId = $this->isDeliveryboy() ? $this->returnArray["id"] : 0;
        if ($tokenCollection->getSize() > 0) {
            foreach ($tokenCollection as $eachRow) {
                $token = $this->tokenFactory->create()
                    ->load($eachRow->getId());
                $this->applyRequestToToken($token);
                $token->save();
            }
        } else {
            $token = $this->tokenFactory
                ->create()
                ->setOs($this->os)
                ->setToken($this->token);
                $this->applyRequestToToken($token);
                $token->save();
        }
    }

    /**
     * @return bool
     */
    protected function isDeliveryboy(): bool
    {
        return !$this->returnArray["isAdmin"];
    }

    /**
     * @param \Webkul\DeliveryBoy\Model\Token $token
     * @return \Webkul\DeliveryBoy\Model\Token
     */
    protected function applyRequestToToken(\Webkul\DeliveryBoy\Model\Token $token): \Webkul\DeliveryBoy\Model\Token
    {
        $isAdmin = (int)$this->returnArray["isAdmin"] ?: 0;
        $token->setIsAdmin($isAdmin);
        $deliveryboyId = $this->returnArray["isAdmin"] ? 0 : $this->returnArray["id"];
        $token->setDeliveryboyId($deliveryboyId);

        return $token;
    }
}
