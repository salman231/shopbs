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

namespace Webkul\Rmasystem\Model\Config\Source;

use \Magento\Framework\App\Config\ScopeConfigInterface;
use \Magento\Payment\Model\Config;
use Magento\Store\Model\ScopeInterface;

/**
 * Used in creating options for getting payment method value.
 */
class Paymentmethod
{
    /**
     * @var ScopeConfigInterface
     */
    protected $appConfigScopeConfigInterface;

    /**
     * @var Config
     */
    protected $paymentModelConfig;

    protected $paymentMethodFactory;

    /**
     * @param ScopeConfigInterface $appConfigScopeConfigInterface
     * @param Config $paymentModelConfig
     * @param \Magento\Payment\Model\Method\Factory $paymentMethodFactory
     */
    public function __construct(
        ScopeConfigInterface $appConfigScopeConfigInterface,
        Config $paymentModelConfig,
        \Magento\Payment\Model\Method\Factory $paymentMethodFactory
    ) {
        $this->_appConfigScopeConfigInterface = $appConfigScopeConfigInterface;
        $this->paymentModelConfig = $paymentModelConfig;
        $this->paymentMethodFactory = $paymentMethodFactory;
    }
    
    /**
     * Options getter.
     *
     * @return array
     */
    public function toOptionArray()
    {
        $payments = $this->getActiveMethods();
        $methods = [];
        foreach ($payments as $paymentCode => $paymentModel) {
            $paymentTitle = $this->_appConfigScopeConfigInterface
                            ->getValue('payment/'.$paymentCode.'/title');
                $methods[$paymentCode] = [
                    'label' => $paymentTitle,
                    'value' => $paymentCode
                ];
        }
        return $methods;
    }

    /**
     * Retrieve active system payments
     *
     * @return array
     * @api
     */
    public function getActiveMethods()
    {
        $methods = [];
        $paymentMethods = $this->_appConfigScopeConfigInterface->getValue(
            'payment',
            ScopeInterface::SCOPE_STORE,
            null
        );
        foreach ($paymentMethods as $code => $data) {
            if (isset($data['model'])) {
                /** @var MethodInterface $methodModel Actually it's wrong interface */
                $methodModel = $this->paymentMethodFactory->create($data['model']);
                $methodModel->setStore(null);
                $methods[$code] = $methodModel;
            }
        }
        return $methods;
    }
}
