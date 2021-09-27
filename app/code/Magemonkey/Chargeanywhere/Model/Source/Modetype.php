<?php
namespace Magemonkey\Chargeanywhere\Model\Source;
class Modetype implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return [['value' => 'test', 'label' => 'Test'], ['value' => 'live', 'label' => 'Live']];
    }

}