<?php
namespace Magedelight\MembershipSubscription\Plugin\Magento\Backend\Model\Menu;

class Item
{
    public function afterGetUrl($subject, $result)
    {
        $menuId = $subject->getId();

        if ($menuId == 'Magedelight_MembershipSubscription::documentation') {
            $result = 'http://docs.magedelight.com/display/MAG/Membership+Subscription+-+Magento+2';
        }

        return $result;
    }
}
