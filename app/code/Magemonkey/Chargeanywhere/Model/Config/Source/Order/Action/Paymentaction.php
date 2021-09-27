<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magemonkey\Chargeanywhere\Model\Config\Source\Order\Action;
/**
 * Order Status source model
 */
class Paymentaction  
{
    /**
     * @var string[]
     */
    public function toOptionArray(){
        return [
            ['value' => 'authorize', 'label' => __('Authorize Only')],
            ['value' => 'authorize_capture', 'label' => __('Sale')],
        ];
    }
}
