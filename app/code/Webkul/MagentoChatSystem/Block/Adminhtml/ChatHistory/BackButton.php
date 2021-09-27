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
namespace Webkul\MagentoChatSystem\Block\Adminhtml\ChatHistory;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class BackButton implements ButtonProviderInterface
{
    /**
     * @param \Magento\Framework\App\Response\RedirectInterface $redirect
     */
    public function __construct(
        \Magento\Framework\App\Response\RedirectInterface $redirect
    ) {
        $this->redirect = $redirect;
    }

    /**
     * Get Button Data
     *
     * @return Array
     */
    public function getButtonData()
    {
        return [
            'label' => __('Back'),
            'on_click' => sprintf("location.href = '%s';", $this->redirect->getRefererUrl()),
            'class' => 'back',
            'sort_order' => 10
        ];
    }
}
