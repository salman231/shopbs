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

class ModelBox extends \Magento\Backend\Block\Template
{
    /**
     * @var \Webkul\MagentoChatSystem\Model\ChatDataConfigProvider
     */
    protected $configProvider;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Webkul\MagentoChatSystem\Model\EnableUserConfigProvider $configProvider
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Webkul\MagentoChatSystem\Model\EnableUserConfigProvider $configProvider,
        array $data = []
    ) {
        $this->configProvider = $configProvider;
        parent::__construct($context, $data);
    }

    /**
     * Get Enable Users Config
     *
     * @return array
     */
    public function getEnableUsersConfig()
    {
        return $this->configProvider->getConfig();
    }
}
