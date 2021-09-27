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
namespace Webkul\Rmasystem\Controller\Adminhtml\Order;

use Magento\Framework\App\Action\Context;
use Magento\Customer\Model\Session;
use Webkul\Rmasystem\Model\ResourceModel\Rmaitem\CollectionFactory as ItemCollectionFactory;
use Magento\Sales\Model\OrderRepository;
use Webkul\Rmasystem\Api\AllRmaRepositoryInterface;
use Webkul\Rmasystem\Api\Data\AllrmaInterfaceFactory;

class CreditMemo extends \Webkul\Rmasystem\Controller\Adminhtml\Allrma\Index
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;
    /**
     * @var Session
     */
    protected $session;

    /**
     * @var \Magento\Sales\Controller\Adminhtml\Order\CreditmemoLoader
     */
    protected $memoLoader;

    /**
     * @var \Magento\Sales\Model\Order\Email\Sender\CreditmemoSender
     */
    protected $memoSender;

    /**
     * @var \Magento\Sales\Api\CreditmemoManagementInterface
     */
    protected $creditmemoManagement;

    /**
     * @param Context                           $context
     * @param Session                           $customerSession
     * @param \Webkul\Rmasystem\Helper\Email    $emailHelper
     * @param File                              $fileIo
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        \Webkul\Rmasystem\Helper\Data $helper,
        ItemCollectionFactory $itemCollectionFactory,
        \Webkul\Rmasystem\Api\Data\RmaitemInterfaceFactory $rmaItemDataFactory,
        \Webkul\Rmasystem\Api\RmaitemRepositoryInterface $rmaItemRepository,
        OrderRepository $orderRepository,
        AllRmaRepositoryInterface $rmaRepository,
        AllrmaInterfaceFactory $rmaFactory,
        \Magento\Sales\Controller\Adminhtml\Order\CreditmemoLoader $creditmemoLoader,
        \Magento\Sales\Model\Order\Email\Sender\CreditmemoSender $creditmemoSender,
        \Magento\Sales\Api\CreditmemoManagementInterface $creditmemoManagement
    ) {

        $this->_customerSession = $customerSession;
        $this->helper = $helper;
        $this->itemCollectionFactory = $itemCollectionFactory;
        $this->rmaItemDataFactory = $rmaItemDataFactory;
        $this->rmaItemRepository = $rmaItemRepository;
        $this->orderRepository = $orderRepository;
        $this->rmaRepository = $rmaRepository;
        $this->rmaFactory = $rmaFactory;
        $this->memoLoader = $creditmemoLoader;
        $this->memoSender = $creditmemoSender;
        $this->creditmemoManagement = $creditmemoManagement;
    }

    /**
     * Guest New Rma
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function createCreditMemo($data, $rmaId)
    {
        $error = 0;
        $result = ['msg' => '', 'error' => ''];
        $orderId = $this->rmaRepository->getById($rmaId)->getOrderId();
        try {
            $this->memoLoader->setOrderId($orderId);
            $this->memoLoader->setCreditmemoId("");
            $this->memoLoader->setCreditmemo($data);
            $this->memoLoader->setInvoiceId("");
            $memo = $this->memoLoader->load();
            if ($memo) {
                if (!$memo->isValidGrandTotal()) {
                    $result['msg'] = __('Total must be positive.');
                    $result['error'] = 1;
                    return $result;
                }
                if (!empty($data['comment_text'])) {
                    $memo->addComment(
                        $data['comment_text'],
                        isset($data['comment_customer_notify']),
                        isset($data['is_visible_on_front'])
                    );

                    $memo->setCustomerNote($data['comment_text']);
                    $memo->setCustomerNoteNotify(isset($data['comment_customer_notify']));
                }
                if (isset($data['do_offline'])) {
                    //do not allow online refund for Refund to Store Credit
                    if (!$data['do_offline'] && !empty($data['refund_customerbalance_return_enable'])) {
                        $result['msg'] = __('Cannot create online refund.');
                        $result['error'] = 1;
                        return $result;
                    }
                }

                $memoManagement = $this->creditmemoManagement;
                $memoManagement->refund($memo, (bool)$data['do_offline'], !empty($data['send_email']));

                if (!empty($data['send_email'])) {
                    $this->_memoSender->send($memo);
                }
                $result['msg'] = __('Credit memo generated succesfully.');
                $result['error'] = 0;
                return $result;
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $result['msg'] = $e->getMessage();
            $result['error'] = 1;
        } catch (\Exception $e) {
            $result['msg'] = __('Unable to save credit memo right now.');
            $result['error'] = 1;
        }
        return $result;
    }
}
