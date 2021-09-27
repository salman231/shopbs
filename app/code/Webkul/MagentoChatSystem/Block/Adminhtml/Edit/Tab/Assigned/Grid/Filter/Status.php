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
namespace Webkul\MagentoChatSystem\Block\Adminhtml\Edit\Tab\Assigned\Grid\Filter;

/**
 * Adminhtml assigned chat status filter
 */
class Status extends \Magento\Backend\Block\Widget\Grid\Column\Filter\Select
{
    /**
     * @var array
     */
    protected static $_statuses;

    /**
     * @return void
     */
    protected function _construct()
    {
        self::$_statuses = [
            null => null,
            \Webkul\MagentoChatSystem\Model\AssignedChat::STATUS_ACTIVE => __('Online'),
            \Webkul\MagentoChatSystem\Model\AssignedChat::STATUS_DISABLED => __('Offline'),
            \Webkul\MagentoChatSystem\Model\AssignedChat::STATUS_BUSY => __('Busy')
        ];
        parent::_construct();
    }

    /**
     * Get Options
     *
     * @return array
     */
    protected function _getOptions()
    {
        $options = [];
        foreach (self::$_statuses as $status => $label) {
            $options[] = ['value' => $status, 'label' => __($label)];
        }

        return $options;
    }

    /**
     * @return array|null
     */
    public function getCondition()
    {
        return $this->getValue() === null ? null : ['eq' => $this->getValue()];
    }
}
