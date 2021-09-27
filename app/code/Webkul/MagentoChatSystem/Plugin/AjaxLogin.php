<?php
/**
 * Webkul Software.
 *
 * @category Webkul
 * @package Webkul_MagentoChatSystem
 * @author Webkul
 * @copyright Copyright (c) WebkulSoftware Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 *
 */
namespace Webkul\MagentoChatSystem\Plugin;

class AjaxLogin
{
    public function __construct(
        \Webkul\MagentoChatSystem\Helper\Data $helper
    ) {
        $this->helper = $helper;
    }

    public function afterExecute(
        \Magento\Customer\Controller\Ajax\Login $login,
        $result
    ) {
        $this->helper->cacheFlush();
        return $result;
    }
}
