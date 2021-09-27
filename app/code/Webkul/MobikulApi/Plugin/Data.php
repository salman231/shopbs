<?php

namespace Webkul\MobikulApi\Plugin;

class Data
{
    public function afterIsAuthorized(\Webkul\MobikulCore\Helper\Data $subject, $result)
    {
        $urlInterface = \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Magento\Framework\UrlInterface::class);
        $url = $urlInterface->getCurrentUrl();
        if (stripos($url, "download") !== false || stripos($url, "print") !== false) {
            $result['code'] = 1;
            return $result;
        }
        return $result;
    }
}
