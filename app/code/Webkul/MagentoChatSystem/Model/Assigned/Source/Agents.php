<?php
/**
 * Webkul Software
 *
 * @category Webkul
 * @package Webkul_MagentoChatSystem
 * @author Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */
namespace Webkul\MagentoChatSystem\Model\Assigned\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Webkul\MagentoChatSystem\Model\ResourceModel\AgentData\CollectionFactory;

/**
 * Class Agents
 */
class Agents implements OptionSourceInterface
{
    /**
     * @var CollectionFactory
     */
    protected $agentCollection;

    /**
     * @var array
     */
    protected $options;

    /**
     * @param CollectionFactory $agentCollection
     */
    public function __construct(CollectionFactory $agentCollection)
    {
        $this->agentCollection = $agentCollection;
    }
    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        if ($this->options !== null) {
            return $this->options;
        }
        $agents = $this->agentCollection->create();
        $options = [];
        foreach ($agents as $agent) {
            $options[] = [
                'label' => $agent->getAgentName(),
                'value' => $agent->getAgentId(),
            ];
        }
        $this->options = $options;
        return $this->options;
    }
}
