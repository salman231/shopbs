<?php
/**
* Magedelight
* Copyright (C) 2017 Magedelight <info@magedelight.com>
*
* @category Magedelight
* @package Magedelight_MembershipSubscription
* @copyright Copyright (c) 2017 Mage Delight (http://www.magedelight.com/)
* @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
* @author Magedelight <info@magedelight.com>
 */

namespace Magedelight\MembershipSubscription\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

class SalesQuoteMergeBefore implements ObserverInterface
{
    public function execute(EventObserver $observer)
    {
        if ($observer->getSource()->hasItems()) {
            $currentQuote = $observer->getSource();
            $removeProduct = false;
            if (is_object($observer->getQuote()) && $observer->getQuote()->getId()) {
                $oldQuote = $observer->getQuote();
                foreach ($oldQuote->getAllVisibleItems() as $item) {
                    if($item->getProductType() == 'Membership') {
                        $removeProduct = true;
                    }                    
                }                
            }
            if($removeProduct) {
                $currentQuote->removeAllItems();
            }
        }
    }
}

