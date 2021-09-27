<?php
namespace Webkul\MobikulApi\Controller\RMA;

class GetOrderItems extends AbstractRma
{
    public function execute()
    {
        $this->verifyRequest();
        // Checking customer token //////////////////////////////////////////////
        if (!$this->customerId && $this->customerToken != "") {
            $this->returnArray["otherError"] = "customerNotExist";
            throw new \Magento\Framework\Exception\LocalizedException(
                __("As customer you are requesting does not exist, so you need to logout.")
            );
        }
    	if ($this->customerId) {
            $this->returnArray["success"]= true;
            $this->returnArray["message"]= "";
            $orderr= $this->_order->loadByIncrementId($this->orderId);
            $id=$orderr->getId();
            $order = $this->orderRepository->get($id);

            $allowedProductsType = explode(',', $this->RmaHelper->getConfigData('allow_for_rma'));
            $allowedPaymentMethods = explode(',', $this->RmaHelper->getConfigData('payment_allow_for_rma'));
            $orderDetails['orderDetails'] = [];
            $delivery_items = true;
            $orderDetails['orderDetails'] = [];
            $allItems = $order->getAllVisibleItems();
            $totalRmaReturned = 0;
            $orderPaymentMethod = $order->getPayment()->getMethod();
            //payment method check
            if (!in_array($orderPaymentMethod, $allowedPaymentMethods)) {
                $this->returnArray["message"]= "This order's payment method is not allowed in RMA.";
                return $this->getJsonResponse($this->returnArray);
            }
            $itemsdata=[];
            foreach ($allItems as $item) {
                $disable = false;
                $qtyOrder = $item->getQtyOrdered();
                $qtyInvoiced = $item->getQtyInvoiced();
                if (!$item->getProduct()) {
                    continue;
                }
                if ($item->getProductType() == 'virtual' || $item->getProductType() == 'downloadable' || $item->getProductType() == 'Membership') {
                    $delivery_items = false;
                }
                if (!in_array($item->getProductType(), $allowedProductsType) || $item->getParentItem()) {
                    continue;
                }

                $returnedQty = 0;
                $activeRmaFound = false;
                $rmaStatus = null;
                $activeRmaQty = 0;

                $itemCollection = $this->_itemCollectionFactory->create()
                    ->addFieldToFilter('order_id', ['eq' => $id])
                    ->addFieldToFilter('item_id', ['eq' => $item->getItemId()]);

                if (count($itemCollection)) {
                    if ($this->RmaHelper->getConfigData('active_after_decline')
                        || $this->RmaHelper->getConfigData('active_after_cancel')) {
                        foreach ($itemCollection as $rmaItem) {
                            $rma = $this->_allRmaFactory->create()->load($rmaItem->getRmaId());
                            if ($rma->getStatus() != 3 && $rma->getStatus() != 4) {
                                $activeRmaFound = true;
                                $activeRmaQty = $rmaItem->getQty();
                                break;
                            }
                            $rmaStatus = $rma->getStatus();
                        }
                    }
                    
                    $itemCollection->addFieldToSelect('qty');
                    $returnedQuanties = $itemCollection->getColumnValues('qty');
                    foreach ($returnedQuanties as $value) {
                        $returnedQty+=$value;
                    }
                }

                /**
                 * check item should allow to RMA
                 */
                 $this->_checkDisableRmaItem(
                     $item,
                     $rmaStatus,
                     $activeRmaFound,
                     $returnedQty,
                     $activeRmaQty,
                     $disable,
                     $totalRmaReturned
                );
                
                $product = $this->_productRepository->getById(
                    $item->getProductId()
                );
                //category filter
                $catIds = $product->getCategoryIds();
                if (!$this->RmaHelper->isCategoryAllowed($catIds)) {
                    continue;
                }

                $url = $product->getProductUrl();

                //$storeId = $this->storeManager->getStore()->getId();
                // emulate the frontend environment
                //$this->appEmulation->startEnvironmentEmulation($storeId, \Magento\Framework\App\Area::AREA_FRONTEND, true);
                $imageUrl = $this->_imageHelper->init($product, 'product_page_image_small')
                                ->setImageFile($product->getImage())
                                ->keepAspectRatio(true)
                                ->resize(100, 100)
                                ->getUrl();
                //$this->appEmulation->stopEnvironmentEmulation();

                $price = strip_tags($order->formatPrice($item->getPrice() - $item->getDiscountAmount()));
                $result=[
                        'image' => $imageUrl,
                        'name' => $item->getName(),
                        'sku' => $item->getSku(),
                        'qty' => (int) $item->getQtyOrdered(),
                        'itemid' => $item->getItemId(),
                        'product_id' => $item->getProductId(),
                        'price' => $price,
                        'returnedQty' => $returnedQty,
                        'disabled' => $disable,
                        'error' => false
                ];
                $itemsdata[]=$result;
                
            }
            $this->returnArray["data"]["items"]=$itemsdata;
        } else {
            $this->returnArray["message"]="Invalid Customer.";
        }
        //$encodedData = $this->jsonHelper->jsonEncode($this->returnArray);
        return $this->getJsonResponse($this->returnArray);
    }
    protected function _checkDisableRmaItem(
        $item,
        $rmaStatus,
        $activeRmaFound,
        $returnedQty,
        $activeRmaQty,
        &$disable,
        &$totalRmaReturned
    ) {
        if ($returnedQty == $item->getQtyOrdered()) {
            $disable = true;
            $totalRmaReturned++;
        }
        if ($activeRmaFound == true && $activeRmaQty == $item->getQtyOrdered()) {
            $disable = true;
        } elseif ($activeRmaFound == false && $rmaStatus == 3 && $this->RmaHelper->getConfigData('active_after_decline')) {
            $disable = false;
            $totalRmaReturned--;
        } elseif ($activeRmaFound == false && $rmaStatus == 4 && $this->RmaHelper->getConfigData('active_after_cancel')) {
            $disable = false;
            $totalRmaReturned--;
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
        if ($this->getRequest()->getMethod() == "GET" && $this->wholeData) {
            $this->storeId = $this->wholeData["storeId"] ?? 1;
            $this->forDashboard = $this->wholeData["forDashboard"] ?? 0;
            $this->customerToken = $this->wholeData["customerToken"] ?? "";
            $this->orderId = $this->wholeData["orderId"] ?? 0;
            $this->customerId = $this->helper->getCustomerByToken($this->customerToken);
        } else {
            throw new \BadMethodCallException(__("Invalid Request"));
        }
    }
}

