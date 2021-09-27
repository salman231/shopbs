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
namespace Webkul\DeliveryBoy\Model\Deliveryboy\Source;

use Magento\Framework\Data\OptionSourceInterface;
use \Webkul\DeliveryBoy\Model\Deliveryboy;

class Type implements OptionSourceInterface
{
    /**
     * @var Deliveryboy
     */
    protected $deliveryboy;

    /**
     * @param Deliveryboy $deliveryboy
     */
    public function __construct(Deliveryboy $deliveryboy)
    {
        $this->deliveryboy = $deliveryboy;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $availableOptions = $this->deliveryboy->getAvailableTypes();
        $options = [];
        foreach ($availableOptions as $key => $value) {
            $options[] = [
                "label" => $value,
                "value" => $key
            ];
        }
        return $options;
    }
}
