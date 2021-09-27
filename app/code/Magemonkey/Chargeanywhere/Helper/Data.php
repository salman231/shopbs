<?php
namespace Magemonkey\Chargeanywhere\Helper;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Helper\AbstractHelper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
	 protected $_scopeConfig;
	 public function __construct(
		 \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
	 ) 
	 {
	 	$this->_scopeConfig = $scopeConfig;
	 }
	public function getConfig($field)
    {        
		return $this->_scopeConfig->getValue('payment/chargeanywhere/'.$field, ScopeInterface::SCOPE_STORE);
    }
    public function getModeType()
    {   
    	$modetype = $this->getConfig('mode_type');
    	if($modetype == 'test'){
    		return 'https://webtest.chargeanywhere.com/ChargeAnywhereManager/PaymentForm/PaymentForm.asp';
    	}else{
    		return 'https://www.chargeanywhere.com/ChargeAnywhereManager/PaymentForm/PaymentForm.asp';
    	}
		
    }
}
?>