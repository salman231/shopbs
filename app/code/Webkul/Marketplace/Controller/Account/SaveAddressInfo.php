<?php

namespace Webkul\Marketplace\Controller\Account;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;
use Magento\Framework\App\RequestInterface;
use Webkul\Marketplace\Helper\Data as HelperData;
use Magento\Customer\Model\Url as CustomerUrl;
use Webkul\Marketplace\Model\SellerFactory;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\Data\AddressInterfaceFactory;
/**
 * Webkul Marketplace Account Save Payment Information Controller.
 */
class SaveAddressInfo extends \Magento\Customer\Controller\AbstractAccount
{
    /**
     * @var Magento\Customer\Api\AccountManagementInterface
     */
    protected $accountManagement;
     /**
     * @var AddressInterfaceFactory
     */
    private $dataAddressFactory;
 
    /**
     * @var AddressRepositoryInterface
     */
    private $addressRepository;
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Framework\Data\Form\FormKey\Validator
     */
    protected $_formKeyValidator;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_date;

    /**
     * @var HelperData
     */
    protected $helper;

    /**
     * @var CustomerUrl
     */
    protected $customerUrl;

    /**
     * @var SellerFactory
     */
    protected $sellerModel;

    /**
     * @param Context                                    $context
     * @param Session                                    $customerSession
     * @param FormKeyValidator                           $formKeyValidator
     * @param Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param HelperData                                 $helper
     * @param CustomerUrl                                $customerUrl
     * @param SellerFactory                              $sellerModel
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        FormKeyValidator $formKeyValidator,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        HelperData $helper,
        CustomerUrl $customerUrl,
        SellerFactory $sellerModel,
        AddressInterfaceFactory $dataAddressFactory,
        AddressRepositoryInterface $addressRepository,
        \Magento\Customer\Api\AccountManagementInterface $accountManagement
    ) {
        $this->_customerSession = $customerSession;
        $this->_formKeyValidator = $formKeyValidator;
        $this->_date = $date;
        $this->helper = $helper;
        $this->customerUrl = $customerUrl;
        $this->sellerModel = $sellerModel;
        $this->dataAddressFactory = $dataAddressFactory;
        $this->addressRepository = $addressRepository;
        $this->accountManagement = $accountManagement;
        parent::__construct(
            $context
        );
    }

    /**
     * Retrieve customer session object.
     *
     * @return \Magento\Customer\Model\Session
     */
    protected function _getSession()
    {
        return $this->_customerSession;
    }

    /**
     * Check customer authentication.
     *
     * @param RequestInterface $request
     *
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function dispatch(RequestInterface $request)
    {
        $loginUrl = $this->customerUrl->getLoginUrl();

        if (!$this->_customerSession->authenticate($loginUrl)) {
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
        }

        return parent::dispatch($request);
    }

    /**
     * Seller's SavePaymentInfo action.
     *
     * @return \Magento\Framework\Controller\Result\RedirectFactory
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        if ($this->getRequest()->isPost()) {
            try {
                if (!$this->_formKeyValidator->validate($this->getRequest())) {
                    return $this->resultRedirectFactory->create()->setPath(
                        '*/*/editProfile',
                        ['_secure' => $this->getRequest()->isSecure()]
                    );
                }
                $fields = $this->getRequest()->getParams();
                $sellerId = $this->_getSession()->getCustomerId();

                $customerId = $sellerId;

                $selleraddress=$this->getSellerBillingAddress($customerId);

                //echo $fields['supplier_address_zipcode'];
                //exit();

                if(!empty($selleraddress)){
                    $addressId=$selleraddress->getId();
                    $selleraddress = $this->addressRepository->getById($addressId);
                    //$selleraddress = $this->dataAddressFactory->loadById($addressId);
                    
                    //exit();
                    $selleraddress->setFirstname($fields['supplier_address_fname']);
                    $selleraddress->setLastname($fields['supplier_address_lname']);
                    $selleraddress->setTelephone($fields['supplier_address_phone']);
                    $street[] = $fields['supplier_address_street'];//pass street as array
                
                $selleraddress->setStreet($street);
                // Pass dynamica Value
                $selleraddress->setCity($fields['supplier_address_city']);
                $selleraddress->setCountryId($fields['supplier_address_country']);
                $selleraddress->setPostcode($fields['supplier_address_zipcode']);
                $selleraddress->setRegionId($fields['supplier_address_state']);
                //$address->setIsDefaultShipping(1);
                //$address->setIsDefaultBilling(1);
                //$address->setCustomerId($customerId);
                $this->addressRepository->save($selleraddress);
                //$this->addressRepository->save($address);
                }
                else{
                $address = $this->dataAddressFactory->create();
                $address->setFirstname($fields['supplier_address_fname']);
                $address->setLastname($fields['supplier_address_lname']);
                $address->setTelephone($fields['supplier_address_phone']);
                $street[] = $fields['supplier_address_street'];//pass street as array
                
                $address->setStreet($street);
                // Pass dynamica Value
                $address->setCity($fields['supplier_address_city']);
                $address->setCountryId($fields['supplier_address_country']);
                $address->setPostcode($fields['supplier_address_zipcode']);
                $address->setRegionId($fields['supplier_address_state']);
                $address->setIsDefaultShipping(1);
                $address->setIsDefaultBilling(1);
                $address->setCustomerId($customerId);
                $this->addressRepository->save($address);
                }

                

                $collection = $this->sellerModel->create()
                              ->getCollection()
                              ->addFieldToFilter('seller_id', $sellerId);
                $autoIds = $collection->getAllIds();
                foreach ($autoIds as $autoId) {
                    $value = $this->sellerModel->create()->load($autoId);
                    $value->setSupplierAddressFname($fields['supplier_address_fname']);
                    $value->setSupplierAddressLname($fields['supplier_address_lname']);
                    $value->setSupplierAddressStreet($fields['supplier_address_street']);
                    $value->setSupplierAddressCity($fields['supplier_address_city']);
                    $value->setSupplierAddressCountry($fields['supplier_address_country']);
                    $value->setSupplierAddressState($fields['supplier_address_state']);
                    $value->setSupplierAddressZipcode($fields['supplier_address_zipcode']);
                    $value->setSupplierAddressPhone($fields['supplier_address_phone']);
                    $value->setUpdatedAt($this->_date->gmtDate());
                    $value->save();
                }
                // clear cache
                $this->helper->clearCache();
                $this->messageManager->addSuccess(
                    __('Address information was successfully saved')
                );

                return $this->resultRedirectFactory->create()->setPath(
                    '*/*/editProfile',
                    ['_secure' => $this->getRequest()->isSecure()]
                );
            } catch (\Exception $e) {
                
                $this->messageManager->addError($e->getMessage());

                return $this->resultRedirectFactory->create()->setPath(
                    '*/*/editProfile',
                    ['_secure' => $this->getRequest()->isSecure()]
                );
            }
        } else {
            return $this->resultRedirectFactory->create()->setPath(
                '*/*/editProfile',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        }
    }

    public function getSellerBillingAddress($sellerid)
    {
       /* $customer = $this->_customerFactory->getById($sellerid);    //insert customer id
        $billingAddressId = $customer->getDefaultBilling();
        $billingAddress = $this->_addressFactory->getById($billingAddressId);*/
        $billingAddress = $this->accountManagement->getDefaultBillingAddress($sellerid);
        return $billingAddress;
    }
}
