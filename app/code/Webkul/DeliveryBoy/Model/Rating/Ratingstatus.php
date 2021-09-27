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

use Magento\Framework\Data\OptionSourceInterface;

class Ratingstatus implements OptionSourceInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        return $objectManager->get(\Magento\Review\Helper\Data::class)
            ->getReviewStatusesOptionArray();
    }
}
