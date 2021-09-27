<?php
/**
 * Dart_Productkeys Status Options Model.
 * @category    Dart
 *
 */
namespace Dart\Productkeys\Model;

use Magento\Framework\Data\OptionSourceInterface;

class Status implements OptionSourceInterface
{
    /**
     * Get Productkeys row status type labels array.
     * @return array
     */
    public function getOptionArray()
    {
        $options = ['1' => __('Used'),'0' => __('Available')];
        return $options;
    }

    /**
     * Get Productkeys row status labels array with empty value for option element.
     *
     * @return array
     */
    public function getAllOptions()
    {
        $res = $this->getOptions();
        array_unshift($res, ['value' => '', 'label' => '']);
        return $res;
    }

    /**
     * Get Productkeys row type array for option element.
     * @return array
     */
    public function getOptions()
    {
        $res = [];
        foreach ($this->getOptionArray() as $index => $value) {
            $res[] = ['value' => $index, 'label' => $value];
        }
        return $res;
    }

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return $this->getOptions();
    }
}
