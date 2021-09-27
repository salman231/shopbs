<?php
/**
 * Webkul Software.
 *
 *
 *
 * @category  Webkul
 * @package   Webkul_MobikulCore
 * @author    Webkul <support@webkul.com>
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html ASL Licence
 * @link      https://store.webkul.com/license.html
 */

namespace Webkul\MobikulCore\Model\Notification\Source;

use Magento\Framework\Data\OptionSourceInterface;

class Status implements OptionSourceInterface
{
    protected $mobikulNotification;

    public function __construct(
        \Webkul\MobikulCore\Model\Notification $mobikulNotification
    ) {
        $this->mobikulNotification = $mobikulNotification;
    }

    public function toOptionArray()
    {
        $availableOptions = $this->mobikulNotification->getAvailableStatuses();
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
