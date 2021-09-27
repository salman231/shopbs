<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Customform
 */


namespace Amasty\Customform\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class Status implements ArrayInterface
{
    const ANSWERED = 1;
    const PENDING = 0;

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::PENDING, 'label' => __('Pending')],
            ['value' => self::ANSWERED, 'label' => __('Answered')],
        ];
    }
}
