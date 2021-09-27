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
 * Class ApplyCoupon
 * to apply coupon in quote
 */
class ApplyCoupon extends AbstractCheckout
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
            if ((bool)$this->removeCoupon) {
                $this->couponCode = "";
            }
            $codeLength = strlen($this->couponCode);
            $isCodeLengthValid = $codeLength && $codeLength <= \Magento\Checkout\Helper\Cart::COUPON_CODE_MAX_LENGTH;
            $itemsCount = $quote->getItemsCount();
            if ($itemsCount) {
                $quote->getShippingAddress()->setCollectShippingRates(true);
                $quote->setCouponCode($isCodeLengthValid ? $this->couponCode : "")->collectTotals();
                $this->quoteRepository->save($quote);
            }
            if ($codeLength) {
                $escaper = $this->escaper;
                if (!$itemsCount) {
                    if ($isCodeLengthValid) {
                        $coupon = $this->couponFactory->create();
                        $coupon->load($this->couponCode, "code");
                        if ($coupon->getId()) {
                            $quote->setCouponCode($this->couponCode)->save();
                            $this->returnArray["success"] = true;
                            $this->returnArray["message"] = __(
                                'You used coupon code "%1".',
                                $escaper->escapeHtml($this->couponCode)
                            );
                        } else {
                            $this->returnArray["message"] = __(
                                'The coupon code "%1" is not valid.',
                                $escaper->escapeHtml($this->couponCode)
                            );
                        }
                    } else {
                        $this->returnArray["message"] = __(
                            'The coupon code "%1" is not valid.',
                            $escaper->escapeHtml($this->couponCode)
                        );
                    }
                } else {
                    if ($isCodeLengthValid && $this->couponCode == $quote->getCouponCode()) {
                        $this->returnArray["success"] = true;
                        $this->returnArray["message"] = __(
                            'You used coupon code "%1".',
                            $escaper->escapeHtml($this->couponCode)
                        );
                    } else {
                        $this->returnArray["message"] = __(
                            'The coupon code "%1" is not valid.',
                            $escaper->escapeHtml($this->couponCode)
                        );
                        $quote->collectTotals();
                        $this->quoteRepository->save($quote);
                    }
                }
            } else {
                $this->returnArray["success"] = true;
                $this->returnArray["message"] = __("You canceled the coupon code.");
            }
            $quote->getShippingAddress()->setCollectShippingRates(true);
            $quote->collectTotals()->save();
            $this->emulate->stopEnvironmentEmulation($environment);
            return $this->getJsonResponse($this->returnArray);
        } catch (\Exception $e) {
            $this->returnArray["message"] = __($e->getMessage());
            $this->helper->printLog($this->returnArray);
            return $this->getJsonResponse($this->returnArray);
        }
    }

    /**
     * Function to verify request
     *
     * @return void|json
     */
    protected function verifyRequest()
    {
        if ($this->getRequest()->getMethod() == "POST" && $this->wholeData) {
            $this->itemId = $this->wholeData["itemId"] ?? 0;
            $this->quoteId = $this->wholeData["quoteId"] ?? 0;
            $this->storeId = $this->wholeData["storeId"] ?? 1;
            $this->couponCode = $this->wholeData["couponCode"] ?? "";
            $this->removeCoupon = $this->wholeData["removeCoupon"] ?? 0;
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
