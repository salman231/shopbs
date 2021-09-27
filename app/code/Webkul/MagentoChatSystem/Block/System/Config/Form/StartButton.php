<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MagentoChatSystem
 * @author    Webkul
 * @copyright Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MagentoChatSystem\Block\System\Config\Form;

class StartButton extends \Magento\Config\Block\System\Config\Form\Field
{
    const BUTTON_TEMPLATE = 'system/config/start_stop_button.phtml';

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Webkul\MagentoChatSystem\Model\ChatDataConfigProvider
     */
    protected $configProvider;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Webkul\MagentoChatSystem\Model\ChatDataConfigProvider $configProvider
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Webkul\MagentoChatSystem\Model\ChatDataConfigProvider $configProvider,
        array $data = []
    ) {
        $this->configProvider = $configProvider;
        parent::__construct($context, $data);
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

        return $this->_scopeConfig->getValue(
            $path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->_storeManager->getStore()
        );
    }
    /**
     * Set template to itself.
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if (!$this->getTemplate()) {
            $this->setTemplate(static::BUTTON_TEMPLATE);
        }

        return $this;
    }
    /**
     * Render button.
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     *
     * @return string
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        // Remove scope label
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();

        return parent::render($element);
    }

    /**
     * Return ajax url for button.
     *
     * @return string
     */
    public function getAjaxStartUrl()
    {
        return $this->getUrl('chatsystem/server/start');
    }

    /**
     * Return ajax url for button.
     *
     * @return string
     */
    public function getAjaxStopUrl()
    {
        return $this->getUrl('chatsystem/server/stop');
    }
    /**
     * Get the button and scripts contents.
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     *
     * @return string
     */
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $this->addData(
            [
                'id' => 'demomanagement_button',
                'onclick' => 'javascript:check(); return false;',
            ]
        );

        return $this->_toHtml();
    }

    /**
     * @return string
     */
    public function getStartButtonLabel()
    {
        return __('Start Server');
    }

    /**
     * @return string
     */
    public function getStopButtonLabel()
    {
        return __('Stop Server');
    }

    /**
     * check server running or not
     *
     * @return bool
     */
    public function isServerRunning()
    {
        return $this->configProvider->isServerRunning();
    }
}
