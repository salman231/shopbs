<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Customform
 */


namespace Amasty\Customform\Model\ResourceModel\Form\Element\Option;

class Collection extends \Magento\Cms\Model\ResourceModel\Page\Collection
{
    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            'Amasty\Customform\Model\Form\Element\Option',
            'Amasty\Customform\Model\ResourceModel\Form\Element\Option'
        );
    }

    public function getFieldsByElementId($elementId)
    {
        $this->addFieldToFilter('element_id', $elementId);
        return $this;
    }
}
