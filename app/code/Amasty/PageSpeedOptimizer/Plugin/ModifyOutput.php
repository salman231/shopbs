<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_PageSpeedOptimizer
 */


namespace Amasty\PageSpeedOptimizer\Plugin;

/**
 * Class ModifyOutput
 *
 * @package Amasty\PageSpeedOptimizer
 */
class ModifyOutput
{
    /**
     * @var \Amasty\PageSpeedOptimizer\Model\ConfigProvider
     */
    private $configProvider;

    /**
     * @var \Amasty\PageSpeedOptimizer\Model\Output\OutputCompositeInterface
     */
    private $outputChain;

    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    public function __construct(
        \Amasty\PageSpeedOptimizer\Model\ConfigProvider $configProvider,
        \Amasty\PageSpeedOptimizer\Model\Output\OutputCompositeInterface $outputChain,
        \Magento\Framework\Registry $registry
    ) {
        $this->configProvider = $configProvider;
        $this->outputChain = $outputChain;
        $this->registry = $registry;
    }

    /**
     * @param \Magento\Framework\View\Layout $subject
     * @param string $output
     *
     * @return string
     */
    public function afterGetOutput($subject, $output)
    {
        if (!$this->configProvider->isEnabled() || !$this->registry->registry('amoptimizer_process')) {
            return $output;
        }

        return $this->outputChain->process($output);
    }
}
