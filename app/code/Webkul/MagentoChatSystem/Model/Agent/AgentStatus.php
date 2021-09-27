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
namespace Webkul\MagentoChatSystem\Model\Agent;

class AgentStatus implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * @var \Webkul\MagentoChatSystem\Model\AssignedData
     */
    protected $agent;

    /**
     * Constructor
     *
     * @param
     */
    public function __construct(\Webkul\MagentoChatSystem\Model\AgentData $agent)
    {
        $this->agent = $agent;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $availableOptions = $this->agent->getAvailableStatuses();
        foreach ($availableOptions as $key => $value) {
            $options[] = [
                'label' => $value,
                'value' => $key,
            ];
        }
        return $options;
    }
}
