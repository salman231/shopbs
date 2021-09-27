<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_PageSpeedOptimizer
 */


namespace Amasty\PageSpeedOptimizer\Model\Output;

/**
 * Class MoveJsProcessor
 *
 * @package Amasty\PageSpeedOptimizer
 */
class MoveJsProcessor implements OutputProcessorInterface
{
    /**
     * @var \Amasty\PageSpeedOptimizer\Model\ConfigProvider
     */
    private $configProvider;

    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    /**
     * @var \Magento\Framework\Code\Minifier\Adapter\Js\JShrink
     */
    private $JShrink;

    public function __construct(
        \Amasty\PageSpeedOptimizer\Model\ConfigProvider $configProvider,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Code\Minifier\Adapter\Js\JShrink $JShrink
    ) {
        $this->configProvider = $configProvider;
        $this->registry = $registry;
        $this->JShrink = $JShrink;
    }

    /**
     * @inheritdoc
     */
    public function process(&$output)
    {
        if ($this->configProvider->isMoveJS()) {
            $requireJs = $this->registry->registry('requireJsScript');
            if ($requireJs) {
                $output .= '%require_js_script% %other_scripts%';

                $scripts = [];
                $output = preg_replace_callback(
                    '/<script.*?>.*?<\/script.*?>/is',
                    function ($script) use (&$scripts) {
                        $scripts[] .= $script[0];
                        return '';
                    },
                    $output
                );

                $scriptsOutput = '';
                foreach ($scripts as $script) {
                    try {
                        $scriptMin = $this->JShrink->minify($script);
                        if (strpos($scriptMin, '<script') === false
                            || strpos($scriptMin, '</script') === false
                        ) {
                            $scriptsOutput .= $script;
                        } else {
                            $scriptsOutput .= $scriptMin;
                        }
                    } catch (\Exception $e) {
                        $scriptsOutput .= $script;
                    }
                }

                $output = str_replace(
                    '%require_js_script% %other_scripts%',
                    '%lazy_before%' . $requireJs . '%lazy_after%' . $scriptsOutput,
                    $output
                );
            }
        }
    }
}
