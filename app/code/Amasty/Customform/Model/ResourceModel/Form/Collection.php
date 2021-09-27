<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Customform
 */


namespace Amasty\Customform\Model\ResourceModel\Form;

class Collection extends \Magento\Cms\Model\ResourceModel\Page\Collection
{
    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Amasty\Customform\Model\Form', 'Amasty\Customform\Model\ResourceModel\Form');
        $this->_setIdFieldName('form_id');
    }
}
