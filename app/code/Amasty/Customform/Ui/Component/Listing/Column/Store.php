<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Customform
 */


namespace Amasty\Customform\Ui\Component\Listing\Column;

class Store extends \Magento\Store\Ui\Component\Listing\Column\Store
{
    /**
     * Fix magento bug with function empty
     *
     * @param array $item
     * @return string
     */
    protected function prepareItem(array $item)
    {
        if ($item[$this->storeKey] == 0) {
            $item[$this->storeKey] = [0];
        }

        return parent::prepareItem($item);
    }
}
