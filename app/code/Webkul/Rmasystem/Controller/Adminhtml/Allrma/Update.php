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
namespace Webkul\Rmasystem\Controller\Adminhtml\Allrma;

use Magento\Backend\App\Action;
use Magento\TestFramework\ErrorLog\Logger;
use Magento\Framework\App\Filesystem\DirectoryList;
use Webkul\Rmasystem\Api\AllRmaRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;

class Update extends \Magento\Backend\App\Action
{
    protected $_filename;

    protected $_emailHelper;

    /**
     * @var \Webkul\Rmasystem\Api\Data\ConversationInterfaceFactory
     */
    protected $conversationDataFactory;

    /**
     * @var \Webkul\Rmasystem\Api\ConversationRepositoryInterface
     */
    protected $conversationRepository;
    /**
     * @var AllRmaRepositoryInterface
     */
    protected $rmaRepository;

    protected $rmaCreditMemo;

    protected $_orderItem;
    /**
     * @var \Webkul\Rmasystem\Helper\Data
     */
    protected $helper;

    /**
     * @var OrderRepositoryInterface
     */
    protected $_orderRepository;

    /**
     *
     * @param Action\Context $context
     * @param \Webkul\Rmasystem\Api\Data\ConversationInterfaceFactory $conversationDataFactory
     * @param \Webkul\Rmasystem\Api\ConversationRepositoryInterface $conversationRepository
     * @param \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory
     * @param \Webkul\Rmasystem\Controller\Adminhtml\Order\CreditMemo $rmaCreditMemo
     * @param OrderRepositoryInterface $orderRepository
     * @param AllRmaRepositoryInterface $rmaRepository
     * @param \Webkul\Rmasystem\Helper\Data $helper
     * @param \Webkul\Rmasystem\Helper\Email $emailHelper
     * @param \Magento\Sales\Model\Order\Item $orderItem
     */
    public function __construct(
        Action\Context $context,
        \Webkul\Rmasystem\Api\Data\ConversationInterfaceFactory $conversationDataFactory,
        \Webkul\Rmasystem\Api\ConversationRepositoryInterface $conversationRepository,
        \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory,
        \Webkul\Rmasystem\Controller\Adminhtml\Order\CreditMemo $rmaCreditMemo,
        OrderRepositoryInterface $orderRepository,
        AllRmaRepositoryInterface $rmaRepository,
        \Webkul\Rmasystem\Helper\Data $helper,
        \Webkul\Rmasystem\Helper\Email $emailHelper,
        \Magento\Sales\Model\Order\Item $orderItem
    ) {
        $this->_emailHelper = $emailHelper;
        $this->helper = $helper;
        $this->conversationDataFactory = $conversationDataFactory;
        $this->conversationRepository = $conversationRepository;
        $this->_fileUploaderFactory = $fileUploaderFactory;
        $this->rmaRepository = $rmaRepository;
        $this->rmaCreditMemo = $rmaCreditMemo;
        $this->_orderRepository = $orderRepository;
        $this->_orderItem = $orderItem;
        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Webkul_Rmasystem::update');
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
        $this->_filename = '';

        $statusFlag = false;
        $deliveryFlag = false;
        
        $rma = $this->rmaRepository->getById($post["rma_id"]);
        
        if (isset($post["admin_consignment_no"])) {
            $rma->setAdminConsignmentNo($post["admin_consignment_no"]);
        }
            
        if ($post["admin_status"] !== 5 || $post["admin_status"] !== 0) {
            if (isset($post["shipping_label"]) && $rma->getShippingLabel() != $post["shipping_label"]) {
                $rma->setShippingLabel($post["shipping_label"]);
            }
        }

        /**
         * If rma resolution type is cancel
         */
        
        if ($rma->getResolutionType() == 3 && $post["admin_status"] == 6) {
            $this->cancelOrder($rma->getOrderId());
            $this->updateRmaStatus($rma, $post);
            $result['error'] = 0;
        } elseif ($rma->getResolutionType() == 0 && $post["admin_status"] == 6) {
            $result = $this->sendCreditMomo($rma, $post);
            if ($result['error']==0) {
                $this->updateRmaStatus($rma, $post);
            }
        } else {
            $this->updateRmaStatus($rma, $post);
            $result['error'] = 0;
        }


        if ($result['error'] == 1) {
            $this->messageManager->addError($result['msg']);
            return  $resultRedirect->setPath("*/allrma/edit", ["id" => $rma->getId()]);
        } else {
            $this->rmaRepository->save($rma);
            $selfEmail = [
                'check' => false,
                'area' => 'backend'
            ];
            if (isset($post['receive_email'])) {
                $selfEmail['check'] = true;
            }
            if ($statusFlag == true || $deliveryFlag == true) {
                $this->_emailHelper->updateRmaEmail($post, $rma, $statusFlag, $deliveryFlag, $selfEmail, $this->_filename);
            } else {
                $this->_emailHelper->newMessageEmail($post, $rma, $selfEmail, $this->_filename);
            }
            $this->messageManager->addSuccess(
                __('RMA Successfully Updated.')
            );
            return $resultRedirect->setPath("*/allrma/edit", ["id" => $rma->getId()]);
        }
    }
    
    /**
     * Update Rma Status by admin
     * @param  WebkulRmasystemApiDataAllrmaInterface $rma
     * @param  array                                 $post
     */
    public function updateRmaStatus(\Webkul\Rmasystem\Api\Data\AllrmaInterface $rma, $post)
    {
        $customMessage = '';
        if (trim($post["message"]) != "") {
            $customMessage = '<p class="msg-content">'.$post["message"].'</p>';
            $this->saveRmaHistory($rma->getRmaId(), $customMessage);
        }
        if ($rma->getAdminStatus() != $post["admin_status"] && $post["admin_status"] != "") {
            $adminStatus = $post['admin_status'];
            if ($adminStatus == 0) {
                $rma->setStatus(0);
            } elseif ($adminStatus == 1 && $rma->getAdminStatus() !== 1) {
                $rma->setStatus(1);
                $message = '<p class="msg-content">'.
                  __('Your Return request has been approved.').'</p>';
                $this->saveRmaHistory($rma->getRmaId(), $message);
            } elseif ($adminStatus == 2 || $adminStatus == 3) {
                $rma->setStatus(1);
            } elseif ($adminStatus == 5 && $rma->getAdminStatus() !== 5) {
                $rma->setStatus(3);
                $rma->setFinalStatus(2);
                $message = '<p class="msg-content">'.
                  __('Your Return request has been declined.').'</p>';
                $this->saveRmaHistory($rma->getRmaId(), $message);
            } elseif ($adminStatus == 6 && $rma->getAdminStatus() !== 6) {
                $rma->setStatus(2);
                $rma->setFinalStatus(3);
                $message = '<p class="msg-content">'.
                  __('Your Return request has been solved.').'</p>';
                $this->saveRmaHistory($rma->getRmaId(), $message);
            } else {
                $rma->setStatus(0);
            }
            $rma->setAdminStatus($adminStatus);
            $statusFlag = true;
        }
    }

    /**
     * Prepare Credit Momo Data
     * @param  WebkulRmasystemApiDataAllrmaInterface $rma
     * @param  array                                 $post
     * @return array
     */
    public function sendCreditMomo(\Webkul\Rmasystem\Api\Data\AllrmaInterface $rma, $post)
    {
        $result['error'] = 0;
        $memoData = [
            'do_offline' => 1,
            'comment_text' => $post["message"],
            'shipping_amount' => 0,
            'adjustment_positive' => 0,
            'adjustment_negative' => 0
        ];
        $negative = 0;
        $totalPrice = 0;
        $allItemRefunded = false;
        if (isset($post['credit_memo_item'])) {
            $totalItems = count($post['credit_memo_item']);
            $refundItemsCount = 0;
            foreach ($post['credit_memo_item'] as $key => $value) {
                if (isset($value['is_return'])) {
                    $bundle = $this->_orderItem->getCollection()
                            ->addFieldToFilter('item_id', $key)
                            ->addFieldToSelect('product_type')
                            ->addFieldToFilter(
                                ['product_type', 'product_type'],
                                [
                                    ['eq' => 'grouped'],
                                    ['eq' => 'bundle']
                                ]
                            );
                    if ($bundle->getSize()) {
                        $itemChild = $this->_orderItem->getCollection()
                            ->addFieldToFilter('parent_item_id', $key)
                            ->addFieldToSelect('item_id');
                        $totalPrice+= $value['price'];

                        if ($itemChild->getSize()) {
                            foreach ($itemChild as $child) {
                                $memoData['items'][$child->getId()] = $value;
                            }
                        } else {
                            $memoData['items'][$key] = $value;
                        }
                    } else {
                        $memoData['items'][$key] = $value;
                        $totalPrice+= $value['price'];
                    }
                    $refundItemsCount++;
                }
            }
            if ($post['payment_type'] == 2) {
                if ($totalPrice && ($totalPrice >= $post['refund_amount'])) {
                    $memoData['adjustment_negative'] = $totalPrice - $post['refund_amount'];
                } else {
                    $memoData['adjustment_negative'] = $post['refund_amount'];
                }
            }
            if ($refundItemsCount > 0) {
                $result = $this->rmaCreditMemo->createCreditMemo($memoData, $post["rma_id"]);
                if ($refundItemsCount == $totalItems) {
                    $allItemRefunded = true;
                }
            } else {
                $result['error'] = 1;
                $result['msg'] = __('No item(s) selected for refund.');
            }
            
            if ($allItemRefunded && !$result['error']) {
                $rma->setStatus(2);
                $rma->setFinalStatus(3);
            }
            if (!$allItemRefunded) {
                $rma->setStatus(1);
                $rma->setFinalStatus(0);
            }
        }
        return $result;
    }

    /**
     * Cancel Order
     * @param  int $orderId
     */
    public function cancelOrder($orderId)
    {
        $order = $this->_orderRepository->get($orderId);

        if ($order->canCancel()) {
            $order->getPayment()->cancel();
            $order->cancel()->save();
        }
    }

    /**
     * Notify message that rma created.
     * @param  int $rmaId
     */
    public function saveRmaHistory($rmaId, $message)
    {
        $attachment = $this->getRequest()->getFiles('attachment');
        $fileName = '';
        $resultRedirect = $this->resultRedirectFactory->create();
        if (isset($attachment['error']) && !$attachment['error']) {
            $result = $this->uploadConversationFile('attachment', $rmaId);
            if ($result['error'] != 1) {
                $fileName = $result['file'];
            }
        }
        $conversationModel = $this->conversationDataFactory->create()
          ->setRmaId($rmaId)
          ->setMessage($message)
          ->setAttachment($fileName)
          ->setCreatedAt(time())
          ->setSender('default');
        try {
            $this->conversationRepository->save($conversationModel);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
            return $resultRedirect->setPath('*/*/index');
        } catch (\Exception $e) {
            $this->messageManager->addException($e, __('Something went wrong while saving the Message.'));
            return $resultRedirect->setPath('*/*/index');
        }
    }

    /**
     * Upload Image of Rma
     *
     * @param string $fileId
     * @param string $uploadPath
     * @param int $count
     */
    protected function uploadConversationFile($fileId, $rmaId)
    {
        $extArray =  explode(',', $this->helper->getConfigData('file_attachment_extension'));
        $path = $this->helper->getConversationDir($rmaId);
        $fileName = '';
        try {
            /** @var $uploader \Magento\MediaStorage\Model\File\Uploader */
            $uploader = $this->_fileUploaderFactory->create(['fileId' => $fileId]);
            $uploader->setAllowedExtensions($extArray);
            $uploader->setAllowRenameFiles(true);
            $uploader->setAllowCreateFolders(true);
            $result = $uploader->save($path);
            $this->_filename = $result['file'];
        } catch (\Exception $e) {
            $result['error'] = 1;
            $this->messageManager->addException($e, __('Filetype not supported.'));
        }
        return $result;
    }
}
