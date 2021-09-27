<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MagentoChatSystem
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MagentoChatSystem\Block\ChatBox;

class ReplyManagement extends \Magento\Framework\View\Element\Template
{
    /**
     * Get Download Image
     *
     * @return string
     */
    public function getDownloadImage()
    {
        return $this->getViewFileUrl('Webkul_MagentoChatSystem::images/download.png');
    }
}
