<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_PageSpeedOptimizer
 */


namespace Amasty\PageSpeedOptimizer\Model\Output;

/**
 * Class MoveFontProcessor
 *
 * @package Amasty\PageSpeedOptimizer
 */
class MoveFontProcessor implements OutputProcessorInterface
{
    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    public function __construct(\Magento\Framework\Registry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * @inheritdoc
     */
    public function process(&$output)
    {
        $fontCssName = $this->registry->registry('mergedCssName');
        $printCss = $this->registry->registry('printCss');

        if ($fontCssName) {
            $fontLink = str_replace(
                $this->basename($fontCssName),
                'fonts_' . $this->basename($fontCssName),
                $fontCssName
            );
            $printCss .= '<link rel="stylesheet"  type="text/css"  media="all" href="' . $fontLink . '" />';
        }

        if ($printCss) {
            $output .= '<noscript id="deferred-css">' . $printCss . '</noscript><script>'
                . 'var loadDeferredStyles = function() {'
                . 'var addStylesNode = document.getElementById("deferred-css");'
                . 'var replacement = document.createElement("div");'
                . 'replacement.innerHTML = addStylesNode.textContent;'
                . 'document.body.appendChild(replacement);'
                . 'addStylesNode.parentElement.removeChild(addStylesNode);'
                . '};'
                . 'window.addEventListener(\'load\', loadDeferredStyles);</script>';
        }
    }

    /**
     * @param string $file
     *
     * @return string
     */
    private function basename($file)
    {
        /** @codingStandardsIgnoreStart */
        $basename = basename($file);
        /** @codingStandardsIgnoreEnd */

        return $basename;
    }
}
