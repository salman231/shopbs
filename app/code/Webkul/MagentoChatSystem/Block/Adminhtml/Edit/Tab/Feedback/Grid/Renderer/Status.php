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
namespace Webkul\MagentoChatSystem\Block\Adminhtml\Edit\Tab\Feedback\Grid\Renderer;

/**
 * Adminhtml assigned chat status
 */
class Status extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * @var array
     */
    protected static $_statuses;

    /**
     * Constructor for Grid Renderer Status
     *
     * @return void
     */
    protected function _construct()
    {
        self::$_statuses = [
            \Webkul\MagentoChatSystem\Model\Agent\FeedbackStatus::STATUS_ENABLED => __('Approved'),
            \Webkul\MagentoChatSystem\Model\Agent\FeedbackStatus::STATUS_DISABLED => __('Disapproved')
        ];
        parent::_construct();
    }

    /**
     * @param \Magento\Framework\DataObject $row
     * @return \Magento\Framework\Phrase
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        return __($this->getStatus($row->getStatus()));
    }

    /**
     * Get Status
     *
     * @param string $status
     * @return \Magento\Framework\Phrase
     */
    public function getStatus($status)
    {
        if (isset(self::$_statuses[$status])) {
            return self::$_statuses[$status];
        }

        return __('Unknown');
    }
}
