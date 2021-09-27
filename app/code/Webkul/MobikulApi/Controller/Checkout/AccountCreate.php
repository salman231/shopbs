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
 * Class AccountCreate
 * To create account of customer while checkout
 */
class AccountCreate extends AbstractCheckout
{
    public function execute()
    {
        try {
            $this->verifyRequest();
            $environment = $this->emulate->startEnvironmentEmulation($this->storeId);
            $this->orderCustomermanager->create($this->orderId);
            $this->returnArray["success"] = true;
            $this->returnArray["message"] = __("A letter with further instructions will be sent to your email.");
            return $this->getJsonResponse($this->returnArray);
        } catch (\Exception $e) {
            $this->returnArray["message"] = __($e->getMessage());
            $this->helper->printLog($this->returnArray);
            return $this->getJsonResponse($this->returnArray);
        }
        $this->emulate->stopEnvironmentEmulation($environment);
        return $this->getJsonResponse($this->returnArray);
    }

    /**
     * Function to verify request
     *
     * @return void|json
     */
    protected function verifyRequest()
    {
        if ($this->getRequest()->getMethod() == "POST" && $this->wholeData) {
            $this->storeId = $this->wholeData["storeId"] ?? 1;
            $this->orderId = $this->wholeData["orderId"] ?? 0;
            if ($this->orderId == 0) {
                throw new \BadMethodCallException(__("Invalid Order"));
            }
        } else {
            throw new \BadMethodCallException(__("Invalid Request"));
        }
    }
}
