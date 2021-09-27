<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_PageSpeedOptimizer
 */


namespace Amasty\PageSpeedOptimizer\Model\Output;

/**
 * Class OutputComposite
 *
 * @package Amasty\PageSpeedOptimizer
 */
class OutputComposite implements OutputCompositeInterface
{
    /**
     * @var OutputProcessorInterface[]
     */
    private $processors;

    public function __construct(
        $processors
    ) {
        $this->processors = $processors;
    }

    /**
     * @inheritdoc
     */
    public function process($output)
    {
        foreach ($this->processors as $processor) {
            $processor->process($output);
        }

        return $output;
    }
}
