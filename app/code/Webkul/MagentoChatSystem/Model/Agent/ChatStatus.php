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
namespace Webkul\MagentoChatSystem\Model\Agent;

class ChatStatus implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * @var \Webkul\MagentoChatSystem\Model\AssignedChat
     */
    protected $chat;

    /**
     * Constructor
     *
     * @param
     */
    public function __construct(\Webkul\MagentoChatSystem\Model\AssignedChat $chat)
    {
        $this->chat = $chat;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options[] = ['label' => '', 'value' => ''];
        $availableOptions = $this->chat->getAvailableStatuses();
        foreach ($availableOptions as $key => $value) {
            $options[] = [
                'label' => $value,
                'value' => $key,
            ];
        }
        return $options;
    }
}
