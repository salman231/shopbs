<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Marketplace
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Marketplace\Block\Account;

use Magento\Framework\App\Request\DataPersistorInterface;

/**
 * Webkul Marketplace Account Editprofile Block
 */
class Editprofile extends \Magento\Framework\View\Element\Template
{
    /**
     * @var Magento\Customer\Api\AccountManagementInterface
     */
    protected $accountManagement;
    protected $_customerFactory;
    protected $_addressFactory;
    protected $_country;
    /**
     * @var \Magento\Directory\Model\ResourceModel\Country\CollectionFactory
     */
    protected $_countryCollectionFactory;

    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * @var \Webkul\Marketplace\Helper\Data
     */
    protected $helper;

    /**
     * @param \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $countryCollectionFactory
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param DataPersistorInterface $dataPersistor
     * @param \Webkul\Marketplace\Helper\Data $helper
     * @param array $data
     */
    public function __construct(
        \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $countryCollectionFactory,
        \Magento\Framework\View\Element\Template\Context $context,
        DataPersistorInterface $dataPersistor,
        \Webkul\Marketplace\Helper\Data $helper,
        \Magento\Directory\Model\Country $country,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerFactory,
        \Magento\Customer\Api\AddressRepositoryInterface $addressFactory,
        \Magento\Customer\Api\AccountManagementInterface $accountManagement,
        array $data = []
    ) {
        $this->_countryCollectionFactory = $countryCollectionFactory;
        $this->dataPersistor = $dataPersistor;
        $this->helper = $helper;
        $this->_country = $country;
        $this->_customerFactory = $customerFactory;
        $this->_addressFactory = $addressFactory;
        $this->accountManagement = $accountManagement;
        parent::__construct($context, $data);
    }

    /**
     * @return \Magento\Directory\Model\ResourceModel\Country\Collection
     */
    public function getCountryCollection()
    {
        $collection = $this->_countryCollectionFactory->create()->loadByStore();
        return $collection;
    }

    public function getRegionCollectionByCountry($countrycode)
    {
        $regionCollection = $this->_country->loadByCode($countrycode)->getRegions();
        
        return $regionCollection;
    }

    public function getSellerBillingAddress($sellerid)
    {
       /* $customer = $this->_customerFactory->getById($sellerid);    //insert customer id
        $billingAddressId = $customer->getDefaultBilling();
        $billingAddress = $this->_addressFactory->getById($billingAddressId);*/
        $billingAddress = $this->accountManagement->getDefaultBillingAddress($sellerid);
        return $billingAddress;
    }

    /**
     * Retrieve list of top destinations countries
     *
     * @return array
     */
    protected function getTopDestinations()
    {
        $destinations = (string)$this->_scopeConfig->getValue(
            'general/country/destinations',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        return !empty($destinations) ? explode(',', $destinations) : [];
    }

    /**
     * Retrieve list of countries option array
     *
     * @return array
     */
    public function getCountryOptionArray()
    {
        return $options = $this->getCountryCollection()
                ->setForegroundCountries($this->getTopDestinations())
                ->toOptionArray();
    }

    public function getPersistentData()
    {
        $partner = $this->helper->getSeller();
        $persistentData = (array)$this->dataPersistor->get('seller_profile_data');
        foreach ($partner as $key => $value) {
            if (empty($persistentData[$key])) {
                $persistentData[$key] = $value;
            }
        }
        $this->dataPersistor->clear('seller_profile_data');
        return $persistentData;
    }
}
