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
namespace Webkul\Rmasystem\Controller\Guest;

use Magento\Framework\App\Action\Context;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\UrlFactory;
use Magento\Framework\Filesystem\Io\File;
use Magento\Framework\Session\SessionManager;

class Saverma extends \Webkul\Rmasystem\Controller\GuestFrontController
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
            $this->messageManager->addException($e, __('Something went wrong while saving the item.'));
        }
    }

    /**
     * Save action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $post = $this->getRequest()->getPost();
        $error =  $this->validatePostValue($post);
        $customFields = $this->helper->getFields();

        if ($error['error']) {
            $this->messageManager->addError(
                __($error['msg'])
            );
            return $resultRedirect->setPath('rmasystem/guest/rmanew/');
        }
        $data = $this->session->getGuestData();
        if (isset($data['email'])) {
            $error = false;
            $order = $this->orderRepository->get($post["order_selection"]);
            $orderItems = $order->getAllVisibleItems();
            $remainsQty = 0;
            if (isset($post['resolution_type'])) {
                foreach ($post["item_checked"] as $key => $item) {
                    $collection = $this->itemCollectionFactory->create()
                        ->addFieldToFilter('order_id', ['eq' => $post["order_id"]])
                        ->addFieldToFilter('item_id', ['eq' => $key]);

                    if ($collection->getSize()) {
                        foreach ($orderItems as $item) {
                            if ($item->getId() == $collection->getFirstItem()->getItemId()) {
                                $remainsQty = $item->getQtyOrdered() - $collection->getFirstItem()->getQty();
                                if ($post["return_item"][$key] > $remainsQty &&
                                    (!$this->helper->getConfigData('active_after_decline') || !$this->helper->getConfigData('active_after_cancel'))
                                ) {
                                    $error = true;
                                }
                            }
                        }
                    }
                }
            }
            if ($error) {
                $this->messageManager->addError(
                    __('Invalid requested item qty(s).')
                );
                return $resultRedirect->setPath('*/guest/rmanew');
            }
            $imageArray = [];
            
            $guestData = $this->session->getGuestData();
            $post['group'] = 'guest';
            $post['name'] = 'Guest User';
            $post['increment_id'] = $order->getIncrementId();
            $post['order_id'] = $order->getId();
            $post['guest_email'] = $guestData['email'];
            $post['image'] = serialize($imageArray);
            $post['status'] = 0;
            $post['admin_status'] = 0;
            $post['created_at'] = time();

            $rmaModel = $this->rmaFactory->create();
            $rmaModel->setData((array)$post);
            try {
                $model = $this->rmaRepository->save($rmaModel);
                $lastRmaId = $model->getId();

                if ($this->getRequest()->getFiles('related_images')) {
                    $imageArray = $this->saveRmaProductImage($post['total_images'], $lastRmaId);
                }

                if (isset($post['item_checked'])) {
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
                return $resultRedirect->setPath('*/guest/rmanew');
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('Something went wrong while saving the RMA.'));
                return $resultRedirect->setPath('*/guest/rmanew');
            }
        } else {
            return $resultRedirect->setPath('*/guest/login');
        }
        return $resultRedirect->setPath('*/guest/rmaview', ['id' => $lastRmaId]);
    }

    /**
     * Rma fields validation
     * @param  array $post
     * @return array
     */
    public function validatePostValue(&$post)
    {
        $error = [
          'error' => false,
          'msg' => ''
        ];
        if (!isset($post['item_checked']) || !is_array($post['item_checked'])) {
            $error = [
              'error' => true,
              'msg' => __('Select item(s) to generate RMA.')
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
                              'msg' => __('Enter quantity for each item.')
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
                              'msg' => __('Select reason to generate RMA.')
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
        if (isset($post['additional_info']) && !$post['additional_info'] == "") {
            $post['additional_info'] = strip_tags($post['additional_info']);
        }
        if (!isset($post['package_condition'])) {
            $post['package_condition'] = 1;
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
