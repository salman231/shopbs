<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_PageSpeedOptimizer
 */


namespace Amasty\PageSpeedOptimizer\Model\Output;

/**
 * Interface OutputCompositeInterface
 *
 * @package Amasty\PageSpeedOptimizer
 */
interface OutputCompositeInterface
{
    /**
     * @param string $output
     *
     * @return string
     */
    public function process($output);
}
