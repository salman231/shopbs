<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Rmasystem
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Rmasystem\Controller\Newrma;

class Saverma extends \Webkul\Rmasystem\Controller\FrontController
{

    /**
     * Save Rma items
     * @param  array $data
     */
    private function saveReturnedItem($data)
    {

        $rmaItemModel = $this->rmaItemDataFactory->create();

        $rmaItemModel->setData($data);

        try {
            $model = $this->rmaItemRepository->save($rmaItemModel);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addException($e, $e->getMessage());
        }
    }
    /**
     * Save action.
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $post = $this->getRequest()->getPost();
        $customFields = $this->helper->getFields();
        $error =  $this->validatePostValue($post);

        

        if ($error['error']) {
            $this->messageManager->addError(
                __($error['msg'])
            );
            return $resultRedirect->setPath('*/newrma/index');
        }

        $error = false;
        $order = $this->orderRepository->get($post["order_selection"]);
        $orderItems = $order->getAllVisibleItems();
        $remainsQty = 0;
        if (isset($post['resolution_type'])) {
            foreach ($post["item_checked"] as $key => $item) {
                $collection = $this->itemCollectionFactory->create()
                    ->addFieldToFilter('order_id', ['eq' => $post["order_selection"]])
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

                        if ($this->helper->getConfigData('active_after_decline')
                            || $this->helper->getConfigData('active_after_cancel')
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
                        } elseif (!$foundActiveRma && $rmaStatus == 3 && $this->helper->getConfigData('active_after_decline')) {
                            $error = false;
                        } elseif (!$foundActiveRma && $rmaStatus == 4 && $this->helper->getConfigData('active_after_cancel')) {
                            $error = false;
                        }
                    }
                }
            }
        }
        if ($error) {
            $this->messageManager->addError(
                __('Invalid requested item qty(s).')
            );
            return $resultRedirect->setPath('*/newrma/index');
        }
        $imageArray = [];

        $tableName = $this->itemCollectionFactory->create()->getResource()->getTable('wk_rma');
        $nextRmaId = $this->resourceModelHelper->getNextAutoincrement($tableName);

        $customerId = $this->_customerSession->getCustomerId();
        $customerName = $this->_customerSession->getCustomer()->getName();
        $post['group'] = 'customer';
        $post['increment_id'] = $order->getIncrementId();
        $post['order_id'] = $order->getId();
        $post['customer_id'] = $customerId;
        $post['status'] = 0;
        $post['name'] = $customerName;
        $post['admin_status'] = 0;
        $post['created_at'] = $this->date->gmtDate();
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
                $this->messageManager->addError($e->getMessage());
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

            $this->saveRmaHistory($lastRmaId, $this->helper->getConfigData('new_rma_message'));

            foreach ($customFields as $field) {
                $name = $field->getInputname();
                if (array_key_exists($name, $post)) {
                    if (($field->getInputType()=='checkbox') || ($field->getInputType()=='multiselect')) {
                        $saveCustom = [
                            'field_id' => $field->getId(),
                            'rma_id' => $lastRmaId,
                            'value' => implode(",", $post[$name])
                        ];
                    } else {
                        $saveCustom = [
                            'field_id' => $field->getId(),
                            'rma_id' => $lastRmaId,
                            'value' => $post[$name]
                        ];
                    }
                    if (($field->getInputType()=='text') || ($field->getInputType()=='textarea')) {
                        $post[$name] = $this->helper->escapeHtml($post[$name]);
                    }
                    $this->saveCustomFieldData($saveCustom);//save field value
                }
            }
            $this->_emailHelper->sendNewRmaEmail($post, $model);
            $this->_emailHelper->sendNewRmaEmailToAdmin($post, $model);

            $barCode = new \Webkul\Rmasystem\Model\Rmaitem\Barcode39($post['increment_id']);
            $barCode->barcode_text_size = 5;
            $barCode->barcode_bar_thick = 4;
            $barCode->barcode_bar_thin = 2;
            $barCodePath = $this->helper->getBarcodeDir();
            $this->_fileIo->mkdir($barCodePath);
            $barCode->draw($barCodePath.$lastRmaId.'.gif');
            $this->messageManager->addSuccess(
                __('RMA Successfully Saved.')
            );
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
            return $resultRedirect->setPath('*/*/index');
        } catch (\Exception $e) {
            $this->messageManager->addException($e, __('Something went wrong while saving the RMA.'));
            return $resultRedirect->setPath('*/*/index');
        }
        return $resultRedirect->setPath('*/viewrma/index', ['id' => $lastRmaId]);
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

    public function saveCustomFieldData($data)
    {
        try {
            $model = $this->fieldValue->create();
            $model->setData($data);
            $model->save();
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        }
    }
}
