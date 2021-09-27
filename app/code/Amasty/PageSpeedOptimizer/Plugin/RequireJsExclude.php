<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_PageSpeedOptimizer
 */


namespace Amasty\PageSpeedOptimizer\Plugin;

/**
 * Class RequireJsExclude excludes requirejs and other js files from assets
 *
 * @package Amasty\PageSpeedOptimizer
 */
class RequireJsExclude
{
    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    /**
     * @var \Amasty\PageSpeedOptimizer\Model\ConfigProvider
     */
    private $configProvider;

    public function __construct(
        \Magento\Framework\Registry $registry,
        \Amasty\PageSpeedOptimizer\Model\ConfigProvider $configProvider
    ) {
        $this->registry = $registry;
        $this->configProvider = $configProvider;
    }

    /**
     * @param \Magento\Framework\View\Page\Config\Renderer $subject
     * @param string $result
     *
     * @return string
     */
    public function afterRenderAssets($subject, $result)
    {
        if (!$this->configProvider->isEnabled() || !$this->registry->registry('amoptimizer_process')) {
            return $result;
        }

        if ($this->configProvider->isMoveJS()) {
            $scripts = '';
            $result = preg_replace_callback(
                '/<script.*?src=".*?".*?>\s*<\/script>/Uis',
                function ($script) use (&$scripts) {
                    $scripts .= $script[0];
                    return '';
                },
                $result
            );

            $after = (string)$this->registry->registry('requireJsScript');
            $this->registry->unregister('requireJsScript');
            $this->registry->register('requireJsScript', $scripts . $after);
        }

        if ($this->configProvider->isMovePrintCss()) {
            $printOut = '';
            $result = preg_replace_callback(
                '/\<link.*?media="print".*?href=".*?".*?\>/Umi',
                function ($print) use (&$printOut) {
                    $printOut .= $print[0];
                    return '';
                },
                $result
            );

            $this->registry->register('printCss', $printOut);
        }

        if ($this->configProvider->isMoveFont() && preg_match('/href="(.*merged.*)".*>/Uis', $result, $m)) {
            $this->registry->register('mergedCssName', $m[1]);
        }

        return $result;
    }

    /**
     * @param \Magento\Framework\View\Page\Config\Renderer $subject
     * @param string $result
     *
     * @return string
     */
    public function afterRenderHeadContent($subject, $result)
    {
        if (!$this->configProvider->isEnabled() || !$this->registry->registry('amoptimizer_process')) {
            return $result;
        }

        if ($this->configProvider->isMoveJS()) {
            $scripts = '';
            $result = preg_replace_callback(
                '/<script.*?src=".*?".*?>\s*<\/script>/Uis',
                function ($script) use (&$scripts) {
                    $scripts .= $script[0];
                    return '';
                },
                $result
            );
            $requireJs = (string)$this->registry->registry('requireJsScript') . $scripts;
            $this->registry->unregister('requireJsScript');
            $this->registry->register('requireJsScript', $requireJs);
        }

        return $result;
    }
}
