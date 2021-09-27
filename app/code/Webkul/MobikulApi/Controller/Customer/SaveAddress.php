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

class SaveAddress extends AbstractCustomer
{
    public function execute()
    {
        try {
            $this->verifyRequest();
            $this->addressData = $this->jsonHelper->jsonDecode($this->addressData);
            $this->addressData["lastname"] = $this->addressData["lastName"];
            $this->addressData["firstname"] = $this->addressData["firstName"];
            $environment = $this->emulate->startEnvironmentEmulation($this->storeId);
            $addressDataArr = [];
            foreach ($this->addressData as $key => $addressValue) {
                $addressDataArr[$key] = $addressValue;
            }
            $customer = $this->customerFactory->create()->load($this->customerId);
            $customerSession = $this->customerSession->setCustomer($customer);
            $address = $this->customerAddress;
            if ($this->addressId != 0) {
                $existsAddress = $customer->getAddressById($this->addressId);
                if ($existsAddress->getId() && $existsAddress->getCustomerId() == $customer->getId()) {
                    $address->setId($existsAddress->getId());
                }
            }
            $errors = [];
            $addressForm = $this->customerForm;
            $addressForm->setFormCode("customer_address_edit")->setEntity($address);
            $addressErrors = $addressForm->validateData($addressDataArr);
            if ($addressErrors !== true) {
                $errors = $addressErrors;
            }
            $addressForm->compactData($addressDataArr);
            $address->setCustomerId($this->customerId)
                ->setIsDefaultBilling($addressDataArr["default_billing"])
                ->setIsDefaultShipping($addressDataArr["default_shipping"]);
            $addressErrors = $address->validate();
            $address->save();
            $this->returnArray["message"] = __("The address has been saved.");
            $this->returnArray["success"] = true;
            $this->emulate->stopEnvironmentEmulation($environment);
            return $this->getJsonResponse($this->returnArray);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->returnArray["message"] = __($e->getMessage());
            $this->helper->printLog($this->returnArray);
            return $this->getJsonResponse($this->returnArray);
        } catch (\Exception $e) {
            $this->returnArray["message"] = __($e->getMessage());
            $this->helper->printLog($this->returnArray);
            return $this->getJsonResponse($this->returnArray);
        }
    }

    /**
     * Verify Request function to verify the request
     *
     * @return void|jSon
     */
    protected function verifyRequest()
    {
        if ($this->getRequest()->getMethod() == "POST" && $this->wholeData) {
            $this->storeId = $this->wholeData["storeId"] ?? 1;
            $this->addressId = $this->wholeData["addressId"] ?? 0;
            $this->addressData = $this->wholeData["addressData"] ?? "[]";
            $this->customerToken = $this->wholeData["customerToken"] ?? "";
            $this->customerId = $this->helper->getCustomerByToken($this->customerToken);
            // Checking customer token //////////////////////////////////////////////
            if (!$this->customerId && $this->customerToken != "") {
                $this->returnArray["otherError"] = "customerNotExist";
                throw new \Magento\Framework\Exception\LocalizedException(
                    __("As customer you are requesting does not exist, so you need to logout.")
                );
            }
        } else {
            throw new \BadMethodCallException(__("Invalid Request"));
        }
    }
}
