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

namespace Webkul\MobikulApi\Controller\Sales;

class GuestReOrder extends \Webkul\MobikulApi\Controller\Customer\AbstractCustomer
{

    protected $quoteFactory;

    public function _construct(
        \Magento\Quote\Model\QuoteFactory $quoteFactory
    ) {
        $this->quoteFactory = $quoteFactory;
    }

    public function execute()
    {
        try {
            $this->verifyRequest();
            $environment = $this->emulate->startEnvironmentEmulation($this->storeId);
            $order = $this->order->loadByIncrementId($this->incrementId);

            $outOfStockItems = [];
            if ($this->quoteId) {
                $quote = $this->quoteFactory->create()->load($this->quoteId);
                if (!$quote->getId()) {
                    $this->returnArray["message"] = __("Quote not found.");
                    return $this->getJsonResponse($this->returnArray);
                }
            } else {
                $quote = $this->quoteFactory->create()
                    ->setStoreId($this->storeId)
                    ->setIsActive(true)
                    ->setIsMultiShipping(false)
                    ->save();
            }
            $quoteId = (int) $quote->getId();
            
            $cart = $this->cartFactory->create()->setQuote($quote);
            $items = $order->getItemsCollection();
            $this->checkoutSession->setQuoteId($quote->getId());
            foreach ($items as $item) {
                $cart->addOrderItem($item);
            }
            
            foreach ($quote->getAllItems() as $quoteItem) {
                if ($quoteItem->getProductId() == $this->checkoutSession->getLastAddedProductId()) {
                    $quoteItem->setQty($quoteItem->getData("qty"))->save(); //To update the quantity in quote item
                }
            }
            $cart->save();
            
            $this->returnArray["cartCount"] = $this->helper->getCartCount($quote);
            $this->returnArray['quoteId'] = $quoteId;
            $this->returnArray["success"] = true;
            $this->returnArray["message"] = __("Product(s) has been added to cart.");
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
            $this->incrementId = $this->wholeData["incrementId"] ?? "";
            $this->quoteId = $this->wholeData["quoteId"] ?? "";
        } else {
            throw new \BadMethodCallException(__("Invalid Request"));
        }
    }
}
