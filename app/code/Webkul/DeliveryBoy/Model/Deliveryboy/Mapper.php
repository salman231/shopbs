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
namespace Webkul\DeliveryBoy\Model\Deliveryboy;

use Magento\Framework\Api\ExtensibleDataObjectConverter;

class Mapper
{
    /**
     * @var ExtensibleDataObjectConverter
     */
    private $extensibleDataObjectConverter;

    /**
     * @param ExtensibleDataObjectConverter $extensibleDataObjectConverter
     */
    public function __construct(ExtensibleDataObjectConverter $extensibleDataObjectConverter)
    {
        $this->extensibleDataObjectConverter = $extensibleDataObjectConverter;
    }

    /**
     * @param  \Webkul\DeliveryBoy\Api\Data\DeliveryboyInterface $deliveryboy
     * @return array
     */
    public function toFlatArray(\Webkul\DeliveryBoy\Api\Data\DeliveryboyInterface $deliveryboy)
    {
        $nestedArray = $this->extensibleDataObjectConverter->toNestedArray(
            $deliveryboy,
            [],
            \Webkul\DeliveryBoy\Api\Data\DeliveryboyInterface::class
        );
        return \Magento\Framework\Convert\ConvertArray::toFlatArray($nestedArray);
    }
}
