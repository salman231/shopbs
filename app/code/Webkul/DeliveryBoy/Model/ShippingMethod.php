<?php
/**
 * Webkul Software.
 *
 * PHP version 7.0+
 *
 * @category  Webkul
 * @package   Webkul_DeliveryBoy
 * @author    Webkul <support@webkul.com>
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html ASL Licence
 * @link      https://store.webkul.com/license.html
 */
namespace Webkul\DeliveryBoy\Model;

use Magento\Shipping\Model\Config;
use Magento\Framework\App\Config\ScopeConfigInterface;

class ShippingMethod implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * @var \Magento\Shipping\Model\Config
     */
    protected $shipconfig;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @param Config $shipconfig Shipping configuration
     * @param ScopeConfigInterface $scopeConfig Scope configuration
     */
    public function __construct(Config $shipconfig, ScopeConfigInterface $scopeConfig)
    {
        $this->shipconfig = $shipconfig;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Function generates array with all active carrier method available
     *
     * @return array
     */
    public function toOptionArray()
    {
        $methods = [];
        $activeCarriers = $this->shipconfig->getActiveCarriers();
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        foreach ($activeCarriers as $carrierCode => $carrierModel) {
            $options = [];
            if ($carrierMethods = $carrierModel->getAllowedMethods()) {
                foreach ($carrierMethods as $methodCode => $method) {
                    $code = $carrierCode . "_" . $methodCode;
                    $options[] = [
                        "value" => $code,
                        "label" => $method
                    ];
                }
                $carrierTitle = $this->scopeConfig->getValue("carriers/" . $carrierCode . "/title");
            }
            $methods[] = [
                "value" => $options,
                "label" => $carrierTitle
            ];
        }
        return $methods;
    }
}
