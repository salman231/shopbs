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
namespace Webkul\MagentoChatSystem\Block\Adminhtml\ChatBox;

class ReplyManagement extends \Magento\Backend\Block\Template
{
    /**
     * Get Attachement Image
     *
     * @return string
     */
    public function getAttachementImage()
    {
        return $this->getViewFileUrl('Webkul_MagentoChatSystem::images/attachment.png');
    }

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
