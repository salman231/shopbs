<?php
namespace Webkul\MobikulApi\Controller\RMA;
class CreateRma extends AbstractRma
{
    public function execute()
    {
        $this->verifyRequest();
        $post=$this->wholeData;
        //$post["rma_id"]=$this->rmaId;
        // Checking customer token //////////////////////////////////////////////
        if (!$this->customerId && $this->customerToken != "") {
            $this->returnArray["otherError"] = "customerNotExist";
            throw new \Magento\Framework\Exception\LocalizedException(
                __("As customer you are requesting does not exist, so you need to logout.")
            );
        }
    	if ($this->customerId) {
            //$this->returnArray = [];
            //$environment = $this->emulate->startEnvironmentEmulation($this->storeId);
            $post = $this->wholeData;
            $this->returnArray["success"]= true;
            $this->returnArray["message"]= "";

            
            $returned_items=$post["items"];
            $returned_items = json_decode($returned_items, true);
            
            $returned_items = $returned_items["items"];
            
            foreach($returned_items as $item){
                $post["item_checked"][$item['item_checked']] = $item['item_checked'];
                $post["return_item"][$item['item_checked']] = $item['qty'];
                $post["item_reason"][$item['item_checked']] = $item['reson'];

            }
            // $this->returnArray["post_data"]=$post;
            // return $this->getJsonResponse($this->returnArray);

            $customFields = $this->RmaHelper->getFields();
            $error =  $this->validatePostValue($post);

            if ($error['error']) {
                $this->returnArray["message"]= $error['msg'];
                return $this->getJsonResponse($this->returnArray);
            }

            $error = false;
            $orderr= $this->_order->loadByIncrementId($this->orderId);
            $id=$orderr->getId();
            $order = $this->orderRepository->get($id);
            //$order = $this->orderRepository->get($post["order_selection"]);
            $orderItems = $order->getAllVisibleItems();
            $remainsQty = 0;
            if (isset($post['resolution_type'])) {
                foreach ($post["item_checked"] as $key => $item) {
                    
                    $collection = $this->_itemCollectionFactory->create()
                        ->addFieldToFilter('order_id', ['eq' => $id])
                        ->addFieldToFilter('item_id', ['eq' => $key]);

                    if ($collection->getSize()) {
                        $rmaItemCollection = $collection;
                        foreach ($orderItems as $item) {
                            $foundActiveRma = false;
                            $activeRmaQty = 0;
                            $rmaStatus = null;
                            if ($item->getId() == $collection->getFirstItem()->getItemId()) {
                                $remainsQty = $item->getQtyOrdered() - $collection->getFirstItem()->getQty();
                                
                                if ($post["return_item"][$key] > $remainsQty) {
                                    $error = true;
                                }
                            }

                            if ($this->RmaHelper->getConfigData('active_after_decline')
                                || $this->RmaHelper->getConfigData('active_after_cancel')
                                ) {
                                foreach ($rmaItemCollection as $rmaItem) {
                                    $allRmaModel = $this->rmaFactory->create()->load($rmaItem->getRmaId());
                                    if ($allRmaModel->getStatus() != 3 && $allRmaModel->getStatus() != 4) {
                                        $foundActiveRma = true;
                                        $activeRmaQty = $rmaItem->getQty();
                                        break;
                                    }
                                    $rmaStatus = $allRmaModel->getStatus();
                                }
                            }

                            if ($foundActiveRma && $activeRmaQty == $item->getQtyOrdered()) {
                                $error = true;
                            } elseif (!$foundActiveRma && $rmaStatus == 3 && $this->RmaHelper->getConfigData('active_after_decline')) {
                                $error = false;
                            } elseif (!$foundActiveRma && $rmaStatus == 4 && $this->RmaHelper->getConfigData('active_after_cancel')) {
                                $error = false;
                            }
                        }
                    }
                }
            }
            if ($error) {
                $this->returnArray["message"]= __('Invalid requested item qty(s).');
                return $this->getJsonResponse($this->returnArray);
            }
            $imageArray = [];

            $tableName = $this->_itemCollectionFactory->create()->getResource()->getTable('wk_rma');
            $nextRmaId = $this->resourceModelHelper->getNextAutoincrement($tableName);

            $customer = $this->_customerRepositoryInterface->getById($this->customerId);
            //$customerName = $customer->getName();
            //$customerEmail = $customer->getEmail();
            $customerId = $this->customerId;
            $fName = $customer->getFirstname();
            $lName = $customer->getLastname();
            $customerName = $fName." ".$lName;
            $post['group'] = 'customer';
            $post['increment_id'] = $order->getIncrementId();
            $post['order_id'] = $order->getId();
            $post['customer_id'] = $customerId;
            $post['status'] = 0;
            $post['name'] = $customerName;
            $post['admin_status'] = 0;
            $post['created_at'] = $this->_date->gmtDate();
            $post['image'] = serialize($imageArray);
            $rmaModel = $this->rmaFactory->create();
            $rmaModel->setData((array)$post);
            $model = $this->rmaRepository->save($rmaModel);
            $lastRmaId = $model->getId();
            //save images
            if ($this->getRequest()->getFiles('related_images')) {
                try {
                    $imageArray = $this->saveRmaProductImage($post['total_images'], $lastRmaId);
                    $model->setImage(serialize($imageArray));
                    $model->save();
                } catch (\Exception $e) {
                    $this->returnArray["message"]=$e->getMessage();
                    
                }
            }
            try {
                if (isset($post['item_checked'])) {
                    foreach ($post['item_checked'] as $key => $item) {
                        $data = [
                            'rma_id' => $lastRmaId,
                            'item_id' => $key,
                            'reason_id' => $post['item_reason'][$key],
                            'qty' => $post['return_item'][$key],
                            'order_id' => $post['order_id'],
                            'rma_reason' => $this->reasonRepository->getById($post['item_reason'][$key])->getReason()
                        ];
                        $this->saveReturnedItem($data);
                    }
                }

                $this->saveRmaHistory($lastRmaId, $this->RmaHelper->getConfigData('new_rma_message'));

                foreach ($customFields as $field) {
                    $name = $field->getInputname();
                    if (array_key_exists($name, $post)) {
                        if (($field->getInputType()=='checkbox') || ($field->getInputType()=='multiselect')) {
                            $saveCustom = [
                                'field_id' => $field->getId(),
                                'rma_id' => $lastRmaId,
                                'value' => $post[$name]
                            ];
                        } else {
                            $saveCustom = [
                                'field_id' => $field->getId(),
                                'rma_id' => $lastRmaId,
                                'value' => $post[$name]
                            ];
                        }
                        if (($field->getInputType()=='text') || ($field->getInputType()=='textarea')) {
                            $post[$name] = $this->RmaHelper->escapeHtml($post[$name]);
                        }
                        $this->saveCustomFieldData($saveCustom);//save field value
                    }
                }
                $this->_emailHelper->sendNewRmaEmail($post, $model, $this->customerId);
                $this->_emailHelper->sendNewRmaEmailToAdmin($post, $model);

                // $barCode = new \Webkul\Rmasystem\Model\Rmaitem\Barcode39($post['increment_id']);
                // $barCode->barcode_text_size = 5;
                // $barCode->barcode_bar_thick = 4;
                // $barCode->barcode_bar_thin = 2;
                // $barCodePath = $this->RmaHelper->getBarcodeDir();
                // $this->_file->mkdir($barCodePath);
                // $barCode->draw($barCodePath.$lastRmaId.'.gif');
                $this->returnArray["message"]=__('RMA Successfully Saved.');
                
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->returnArray["message"] = $e->getMessage();
                return $this->getJsonResponse($this->returnArray);
            } catch (\Exception $e) {
                $this->returnArray["message"] = __('Something went wrong while saving the RMA.');
                return $this->getJsonResponse($this->returnArray);
                
            }
        
           
        } else {
            $this->returnArray["message"]="Invalid Customer.";
        }
        //$encodedData = $this->jsonHelper->jsonEncode($this->returnArray);
        return $this->getJsonResponse($this->returnArray);
    }
    public function saveReturnedItem($data)
    {

        $rmaItemModel = $this->rmaItemDataFactory->create();

        $rmaItemModel->setData($data);

        try {
            $model = $this->rmaItemRepository->save($rmaItemModel);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->returnArray["message"] = $e->getMessage();
            
        } catch (\Exception $e) {
            $this->returnArray["message"] = $e->getMessage();
            
        }
    }
    public function validatePostValue(&$post)
    {
        $error = [
          'error' => false,
          'msg' => ''
        ];
        if (!isset($post['item_checked']) || !is_array($post['item_checked'])) {
            $error = [
              'error' => true,
              'msg' => __('No item(s) select.')
            ];
            return $error;
        }

        if (isset($post['resolution_type'])) {
            foreach ($post['item_checked'] as $itemId) {
                foreach ($post['return_item'] as $key => $value) {
                    if ($itemId == $key) {
                        if (!$value) {
                            $error = [
                              'error' => true,
                              'msg' => __('Enter refund quantity for each item.')
                            ];
                            break;
                        }
                    }
                }
                foreach ($post['item_reason'] as $key => $value) {
                    if ($itemId == $key) {
                        if (!$value) {
                            $error = [
                              'error' => true,
                              'msg' => __('Select reason to refund item.')
                            ];
                            break;
                        }
                    }
                }
            }
        }
        if (isset($post['cancel_reason']) && !$post['cancel_reason']) {
            $error = [
              'error' => true,
              'msg' => __('Select reason to cancel the order.')
            ];
        }
        if (!isset($post['package_condition'])) {
            $post['package_condition'] = 1;
        }
        if (isset($post['additional_info']) && !$post['additional_info'] == "") {
            $post['additional_info'] = strip_tags($post['additional_info']);
        }
        if (isset($post['customer_consignment_no']) && !$post['customer_consignment_no'] == "") {
            $post['customer_consignment_no'] = strip_tags($post['customer_consignment_no']);
        }
        if ($post['resolution_type'] == '' || (isset($post['package_condition']) && $post['package_condition'] == '')) {
            $error = [
              'error' => true,
              'msg' => __('All required fields must have some value.')
            ];
        }
        return $error;
    }
    public function saveRmaProductImage($numberOfImages, $lastRmaId)
    {
          $imageArray = [];
        if ($numberOfImages > 0) {
            $path = $this->RmaHelper->getBaseDir($lastRmaId);
            for ($i = 0; $i < $numberOfImages; $i++) {
                $fileId = "related_images[$i]";
                $this->uploadImage($fileId, $path, $imageArray);
            }
        }
          return $imageArray;
    }
    public function uploadImage($fileId, $path, &$imageArray)
    {
        $extArray = ['jpg','jpeg', 'gif','png'];
        try {
            /** @var $uploader \Magento\MediaStorage\Model\File\Uploader */
            $uploader = $this->_fileUploaderFactory->create(['fileId' => $fileId]);
            $uploader->setAllowedExtensions($extArray);
            $uploader->setAllowRenameFiles(true);
            $uploader->setAllowCreateFolders(true);
            $result = $uploader->save($path);
            $imageArray[$result['file']] = $result['file'];
        } catch (\Exception $e) {
            $this->returnArray["message"]=  __('Something went wrong while saving images.');
            return $this->getJsonResponse($this->returnArray);
            
        }
    }
    public function saveCustomFieldData($data)
    {
        try {
            $model = $this->fieldValue->create();
            $model->setData($data);
            $model->save();
        } catch (\Exception $e) {
            $this->returnArray["message"] = $e->getMessage();
            
        }
    }
    public function getTimestamp($date){
        return $date = $this->_date->timestamp($date);
    }
    public function saveRmaHistory($rmaId, $message)
    {
        $conversationModel = $this->conversationDataFactory->create()
          ->setRmaId($rmaId)
          ->setMessage($message)
          ->setCreatedAt(time())
          ->setSender('default');
        try {
            $this->conversationRepository->save($conversationModel);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->returnArray["message"]=$e->getMessage();
            return $this->getJsonResponse($this->returnArray);
        } catch (\Exception $e) {
            $this->returnArray["message"]="Something went wrong while saving the Message";
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
            $this->storeId = $this->wholeData["storeId"] ?? 1;
            $this->forDashboard = $this->wholeData["forDashboard"] ?? 0;
            $this->customerToken = $this->wholeData["customerToken"] ?? "";
            $this->orderId = $this->wholeData["order_selection"] ?? 0;
            $this->resolutionType = $this->wholeData["resolution_type"] ?? 0;
            $this->itemIds = $this->wholeData["itemIds"] ?? [];
            $this->returnItem = $this->wholeData["return_item"] ?? [];
            //$this->coversationmessage = $this->wholeData["message"] ?? "";
            //$this->solved = $this->wholeData["solved"] ?? 0;
            //$this->pending = $this->wholeData["pending"] ?? 0;
            $this->customerconsignmentno = $this->wholeData["customer_consignment_no"] ?? "";
            //$this->receiveemail = $this->wholeData["receive_email"] ?? 0;
            
            $this->customerId = $this->helper->getCustomerByToken($this->customerToken);
        } else {
            throw new \BadMethodCallException(__("Invalid Request"));
        }
    }
}

