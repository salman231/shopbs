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
namespace Webkul\DeliveryBoy\Model\Rating;

use \Magento\Framework\Api\ExtensibleDataObjectConverter;

class Mapper
{
    /**
     * @var \Magento\Framework\Api\ExtensibleDataObjectConverter
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
     * @param  \Webkul\DeliveryBoy\Api\Data\RatingInterface $rating
     * @return array
     */
    public function toFlatArray(\Webkul\DeliveryBoy\Api\Data\RatingInterface $rating)
    {
        $nestedArray = $this->extensibleDataObjectConverter->toNestedArray(
            $rating,
            [],
            \Webkul\DeliveryBoy\Api\Data\RatingInterface::class
        );
        return \Magento\Framework\Convert\ConvertArray::toFlatArray($nestedArray);
    }
}
