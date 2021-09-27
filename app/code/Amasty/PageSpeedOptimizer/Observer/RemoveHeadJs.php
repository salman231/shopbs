<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_PageSpeedOptimizer
 */


namespace Amasty\PageSpeedOptimizer\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * Class RemoveHeadJs removes javascript files from page HEAD tag
 *
 * @package Amasty\PageSpeedOptimizer
 */
class RemoveHeadJs implements ObserverInterface
{
    /**
     * @var \Amasty\PageSpeedOptimizer\Model\ConfigProvider
     */
    private $configProvider;

    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    public function __construct(
        \Magento\Framework\Registry $registry,
        \Amasty\PageSpeedOptimizer\Model\ConfigProvider $configProvider
    ) {
        $this->configProvider = $configProvider;
        $this->registry = $registry;
    }

    /**
     * @param Observer $observer
     *
     * @return void
     */
    public function execute(Observer $observer)
    {
        /** @var \Magento\Framework\View\Element\BlockInterface $block */
        $block = $observer->getData('block');
        if ($block->getNameInLayout() !== 'head.additional') {
            return;
        }

        if (!$this->configProvider->isEnabled() || $this->registry->registry('amoptimizer_continue')) {
            return;
        }

        if ($this->configProvider->isMoveJS()) {
            $transport = $observer->getData('transport');
            $scripts = '';
            $transport->setHtml(
                preg_replace_callback(
                    '/<script.*?>.*?<\/script>/Uis',
                    function ($script) use (&$scripts) {
                        $scripts .= $script[0];
                        return '';
                    },
                    $transport->getHtml()
                )
            );

            $requireJs = (string)$this->registry->registry('requireJsScript') . $scripts;
            $this->registry->unregister('requireJsScript');
            $this->registry->register('requireJsScript', $requireJs);
        }
    }
}
