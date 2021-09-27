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
namespace Webkul\MagentoChatSystem\Block\Adminhtml\Edit\Tab;

use Magento\Backend\Block\Template\Context;
use Magento\Framework\Registry;
use Magento\Ui\Component\Layout\Tabs\TabWrapper;

/**
 * Class FeedbackTab
 */
class FeedbackTab extends TabWrapper
{
    /**
     * Core registry
     *
     * @var Registry
     */
    protected $coreRegistry = null;

    /**
     * @var bool
     */
    protected $isAjaxLoaded = true;

    /**
     * Constructor
     *
     * @param Context $context
     * @param Registry $registry
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        array $data = []
    ) {
        $this->coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * @inheritdoc
     */
    public function canShowTab()
    {
        return $this->coreRegistry->registry('agent_data');
    }

    /**
     * Return Tab label
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('Agent Feedbacks');
    }

    /**
     * Return URL link to Tab content
     *
     * @return string
     */
    public function getTabUrl()
    {
        return $this->getUrl('chatsystem/agent_view/feedback', ['_current' => true]);
    }
}
