<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Customform
 */


namespace Amasty\Customform\Model\Config\Source;

class Form implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var \Amasty\Customform\Model\ResourceModel\Form\CollectionFactory
     */
    private $collectionFactory;

    public function __construct(
        \Amasty\Customform\Model\ResourceModel\Form\CollectionFactory $collectionFactory
    ) {
        $this->collectionFactory = $collectionFactory;
    }

    public function toOptionArray()
    {
        $result = [];
        $collection = $this->collectionFactory->create();
        foreach ($collection as $item) {
            $result[] = ['value' => $item->getFormId(), 'label' => $item->getTitle()];
        }

        return $result;
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
