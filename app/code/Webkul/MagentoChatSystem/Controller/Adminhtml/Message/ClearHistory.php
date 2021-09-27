<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MagentoChatSystem
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MagentoChatSystem\Controller\Adminhtml\Message;

use Webkul\MagentoChatSystem\Api\CustomerDataRepositoryInterface;
use Magento\Customer\Model\CustomerFactory;
use Webkul\MagentoChatSystem\Model\ResourceModel\Message\CollectionFactory;

class ClearHistory extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var CustomerDataRepositoryInterface
     */
    protected $customerDataRepository;

    /**
     * @var CustomerFactory
     */
    protected $customerFactory;

    /**
     * @var CustomerFactory
     */
    protected $collectionFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param CustomerDataRepositoryInterface $customerDataRepository
     * @param CustomerFactory $customerFactory
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        CustomerDataRepositoryInterface $customerDataRepository,
        CustomerFactory $customerFactory,
        CollectionFactory $collectionFactory
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->customerDataRepository = $customerDataRepository;
        $this->customerFactory = $customerFactory;
        $this->collectionFactory = $collectionFactory;
    }
   
    /**
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $response = new \Magento\Framework\DataObject();
        $response->setError(false);
        $data = $this->getRequest()->getParam('formData');
        if (isset($data['customerId'])) {
            $customer = $this->customerFactory->create()->load($data['customerId']);
            $chatCustomerModel = $this->customerDataRepository->getByCustomerId($data['customerId']);
            $customerUniqueId = $chatCustomerModel->getLastItem()->getUniqueId();

            $messageModel = $this->collectionFactory
                ->create()
                ->addFieldToFilter(
                    ['sender_unique_id', 'receiver_unique_id'],
                    [['eq' => $customerUniqueId], ['eq' => $customerUniqueId]]
                );
            if ($messageModel->getSize()) {
                foreach ($messageModel as $value) {
                    $value->delete();
                }
                $response->setMessage(__('Chat History Deleted.'));
            } else {
                $response->setError(true);
                $response->setMessage(__('Chat history not available.'));
            }
            return $this->resultJsonFactory->create()->setJsonData($response->toJson());
        }
    }
}
