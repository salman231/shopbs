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
namespace Webkul\DeliveryBoy\Plugin\Framework\Api;

class DataObjectHelper
{
    /**
     * @param \Magento\Framework\Api\DataObjectHelper $subject
     * @param \Magento\Quote\Model\Cart\Totals $dataObject
     * @param array $data
     * @param string $interfaceName
     * @return void
     */
    public function beforePopulateWithArray(
        \Magento\Framework\Api\DataObjectHelper $subject,
        $dataObject,
        array $data,
        string $interfaceName
    ) {
        if ($interfaceName == \Magento\Quote\Api\Data\TotalsInterface::class) {
            if ($dataObject instanceof \Magento\Quote\Model\Cart\Totals) {
                if (isset($data["extension_attributes"]) &&
                    $data["extension_attributes"] instanceof \Magento\Quote\Api\Data\AddressExtension
                ) {
                    unset($data["extension_attributes"]);
                }
            }
        }
        return [$dataObject, $data, $interfaceName];
    }
}
