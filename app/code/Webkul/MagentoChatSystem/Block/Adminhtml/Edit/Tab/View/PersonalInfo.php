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
namespace Webkul\MagentoChatSystem\Block\Adminhtml\Edit\Tab\View;

use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Adminhtml agent view personal information block.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PersonalInfo extends \Magento\Backend\Block\Template
{
    /**
     * @var \Webkul\MagentoChatSystem\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Webkul\MagentoChatSystem\Helper\Data $helper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Webkul\MagentoChatSystem\Helper\Data $helper,
        array $data = []
    ) {
        $this->coreRegistry = $registry;
        $this->helper = $helper;
        parent::__construct($context, $data);
    }

    /**
     * Get current Edit Agent Data.
     *
     * @return void
     */
    public function getAgent()
    {
        return $this->coreRegistry->registry('agent_data');
    }

    /**
     * get ratings array
     *
     * @return void
     */
    public function getAgentRatings()
    {
        return $this->helper->getAgentRating($this->getAgent()->getAgentId());
    }

    /**
     * Get Total Rating
     *
     * @return int
     */
    public function getTotalRating()
    {
        $ratings = $this->getAgentRatings();
        $total = 0;
        foreach ($ratings as $key => $value) {
            $total+= $value;
        }
        if ($total == 0) {
            $total = 1;
        }
        return $total;
    }

    /**
     * Get Rating Total Count
     *
     * @param int $index
     * @return int
     */
    public function getRatingTotalCount($index)
    {
        return $this->getAgentRatings()[$index];
    }

    /**
     * Get Rating Percent By Value
     *
     * @param int $value
     * @return int
     */
    public function getRatingPercentByValue($value)
    {
        $percent = ((int) $this->getAgentRatings()[$value]) * 100 / $this->getTotalRating();
        return $percent;
    }

    /**
     * Get Average Rating
     *
     * @return int
     */
    public function getAverageRating()
    {
        $ratings = $this->getAgentRatings();
        $total = 0;
        foreach ($ratings as $key => $value) {
            $total+= (int) $key * $value;
        }
        $aveg = ($total/ $this->getTotalRating());
        return $aveg;
    }

    /**
     * Get Average Percentage
     *
     * @return int
     */
    public function getAveragePercentage()
    {
        $ratings = $this->getAgentRatings();
        $total = 0;
        foreach ($ratings as $key => $value) {
            $total+= (int) $key * $value;
        }
        
        $averageRating = $total / $this->getTotalRating();
        $maxRating = ($this->getTotalRating()) * 5;
        $totalRating = $averageRating * $this->getTotalRating();
        return ($totalRating / $maxRating) * 100;
    }
}
