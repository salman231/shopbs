<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Customform
 */


namespace Amasty\Customform\Model\Config\Source;

class CustomerGroup implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var \Magento\Customer\Model\Customer\Attribute\Source\Group
     */
    private $groupSource;

    public function __construct(\Magento\Customer\Model\Customer\Attribute\Source\Group $groupSource)
    {
        $this->groupSource = $groupSource;
    }

    public function toOptionArray()
    {
        return array_merge(
            [['value' => 0, 'label' => __('NOT LOGGED IN')]],
            $this->groupSource->getAllOptions()
        );
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        $optionArray = $this->toOptionArray();
        $labels =  array_column($optionArray, 'label');
        $values =  array_column($optionArray, 'value');

        return array_combine($values, $labels);
    }
}
