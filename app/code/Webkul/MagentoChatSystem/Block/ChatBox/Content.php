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

use Magento\Store\Model\ScopeInterface;
use MSP\ReCaptcha\Model\Config;
use MSP\ReCaptcha\Model\LayoutSettings;
use Zend\Json\Json;

class Content extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var LayoutSettings
     */
    protected $configProvider;

    /**
     * @var \Webkul\MagentoChatSystem\Model\ChatDataConfigProvider
     */
    protected $layoutSettings;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Webkul\MagentoChatSystem\Model\ChatDataConfigProvider $configProvider
     * @param LayoutSettings $layoutSettings
     * @param Config $config
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Webkul\MagentoChatSystem\Model\ChatDataConfigProvider $configProvider,
        LayoutSettings $layoutSettings,
        Config $config,
        array $data = []
    ) {
        $this->scopeConfig = $context->getScopeConfig();
        $this->configProvider = $configProvider;
        $this->layoutSettings = $layoutSettings;
        $this->config = $config;
        parent::__construct($context, $data);
    }

    /**
     * Get Js Layout
     *
     * @return array
     */
    public function getJsLayout()
    {
        $layout = Json::decode(parent::getJsLayout(), Json::TYPE_ARRAY);

        if ($this->config->isEnabledFrontend()) {
            $layout['components']['chatbox-content']['children']['msp_recaptcha']['settings']
                = $this->layoutSettings->getCaptchaSettings();
        }

        if (isset($layout['components']['chatbox-content']['children']['msp_recaptcha'])
            && !$this->config->isEnabledFrontend()
        ) {
            unset($layout['components']['chatbox-content']['children']['msp_recaptcha']);
        }

        return Json::encode($layout);
    }

    /**
     * Retrieve information from carrier configuration.
     *
     * @param string $field
     *
     * @return void|false|string
     */
    public function getConfigData($field)
    {
        $path = 'chatsystem/chat_config/'.$field;
        return $this->scopeConfig->getValue(
            $path,
            ScopeInterface::SCOPE_STORE,
            $this->_storeManager->getStore()->getId()
        );
    }

    /**
     * Get ChatBox Config
     *
     * @return array
     */
    public function getChatBoxConfig()
    {
        $configData = $this->configProvider->getConfig();
        $configData['sendImage'] = $this->getViewFileUrl('Webkul_MagentoChatSystem::images/send_message.png');
        $configData['loaderImage'] = $this->getViewFileUrl('Webkul_MagentoChatSystem::images/loader-2.gif');
        $configData['downloadImage'] = $this->getViewFileUrl('Webkul_MagentoChatSystem::images/download.png');
        $configData['attachmentImage'] = $this->getViewFileUrl('Webkul_MagentoChatSystem::images/attachment.png');
        $configData['soundUrl'] = $this->getViewFileUrl('Webkul_MagentoChatSystem::images/sound/notify.ogg');
        $configData['emojiImagesPath'] = $this->getViewFileUrl('Webkul_MagentoChatSystem::images/emojis');
        $configData['maxFileSize'] = $this->getConfigData('max_file_size');
        return $configData;
    }
}
