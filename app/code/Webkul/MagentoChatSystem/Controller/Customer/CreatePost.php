<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MagentoChatSystem
 * @author    Webkul
 * @copyright Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MagentoChatSystem\Controller\Customer;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Model\SessionFactory;
use Magento\Framework\Exception\StateException;
use Magento\Framework\Exception\InputException;

class CreatePost extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerFactory;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var \Magento\Framework\Controller\Result\RawFactory
     */
    protected $resultRawFactory;

    /**
     * @var \Webkul\MagentoChatSystem\Helper\Data
     */
    protected $chatHelper;

    /**
     * @var AccountManagementInterface
     */
    protected $accountManagement;

    /**
     * @var \Webkul\MagentoChatSystem\Model\SaveCustomer
     */
    protected $chatCustomerManagement;

    /**
     * @var SessionFactory
     */
    protected $sessionFactory;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Customer\Api\Data\CustomerInterfaceFactory $customerFactory
     * @param \Magento\Framework\Json\Helper\Data $helper
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
     * @param \Webkul\MagentoChatSystem\Model\SaveCustomer $chatCustomerManagement
     * @param \Webkul\MagentoChatSystem\Helper\Data $chatHelper
     * @param AccountManagementInterface $accountManagement
     * @param SessionFactory $customerSessionFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Api\Data\CustomerInterfaceFactory $customerFactory,
        \Magento\Framework\Json\Helper\Data $helper,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
        \Webkul\MagentoChatSystem\Model\SaveCustomer $chatCustomerManagement,
        \Webkul\MagentoChatSystem\Helper\Data $chatHelper,
        AccountManagementInterface $accountManagement,
        SessionFactory $customerSessionFactory
    ) {
        $this->storeManager = $storeManager;
        $this->customerFactory = $customerFactory;
        $this->helper = $helper;
        $this->chatHelper = $chatHelper;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->resultRawFactory = $resultRawFactory;
        $this->accountManagement = $accountManagement;
        $this->chatCustomerManagement = $chatCustomerManagement;
        $this->sessionFactory = $customerSessionFactory;

        parent::__construct($context);
    }

    public function execute()
    {
        $credentials = null;
        $httpBadRequestCode = 400;

        /** @var \Magento\Framework\Controller\Result\Raw $resultRaw */
        $resultRaw = $this->resultRawFactory->create();
        try {
            $credentials = $this->helper->jsonDecode($this->getRequest()->getContent());
        } catch (\Exception $e) {
            return $resultRaw->setHttpResponseCode($httpBadRequestCode);
        }
        if (!$credentials || $this->getRequest()->getMethod() !== 'POST' || !$this->getRequest()->isXmlHttpRequest()) {
            return $resultRaw->setHttpResponseCode($httpBadRequestCode);
        }

        $response = [
            'errors' => false,
            'message' => __('Login successful.')
        ];
        try {
            $redirectUrl = $this->sessionFactory->create()->getBeforeAuthUrl();
            // Get Website ID
            $websiteId  = $this->storeManager->getWebsite()->getWebsiteId();
            ;
            $name = explode(' ', $credentials['name']);
            if (count($name) == 1) {
                $name[1] = $name[0];
            }
            // Instantiate object (this is the most important part)
            $customer   = $this->customerFactory->create();
            $customer->setWebsiteId($websiteId);

            // Preparing data for new customer
            $customer->setEmail($credentials['username']);
            $customer->setFirstname($name[0]);
            $customer->setLastname($name[1]);
            $password = $credentials['password'];
            
            $customer = $this->accountManagement
                ->createAccount($customer, $password, $redirectUrl);

            $this->_eventManager->dispatch(
                'customer_register_success',
                ['account_controller' => $this, 'customer' => $customer]
            );

            $this->sessionFactory->create()->setCustomerDataAsLoggedIn($customer);
            $this->chatHelper->setCustomerId($customer->getId());
            $customerData = $this->chatCustomerManagement->save($credentials['message'], null, null);

            $response = [
                'errors' => false,
                'message' => __('Registered successful.'),
                'customerData' => $customerData
            ];
            $this->chatHelper->cacheFlush();
            
            /** @var \Magento\Framework\Controller\Result\Json $resultJson */
            $resultJson = $this->resultJsonFactory->create();
            return $resultJson->setData($response);
        } catch (StateException $e) {
            $message = __(
                'There is already an account with this email address.'
            );
            $response = [
                'errors' => true,
                'message' => $message,
            ];
            $resultJson = $this->resultJsonFactory->create();
            return $resultJson->setData($response);
        } catch (InputException $e) {
            $response = [
                'errors' => true,
                'message' => $e->getMessage(),
            ];
            $resultJson = $this->resultJsonFactory->create();
            return $resultJson->setData($response);
        }
    }
}
