<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_PageSpeedOptimizer
 */


namespace Amasty\PageSpeedOptimizer\Plugin;

/**
 * Class CheckPageLayoutOnNull checks if page such as robots.txt
 *
 * @package Amasty\PageSpeedOptimizer
 */
class CheckPageLayoutOnNull
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
     * @param \Magento\Framework\View\Result\Page $subject
     * @param \Magento\Framework\View\Page\Config $result
     *
     * @return \Magento\Framework\View\Page\Config
     */
    public function afterGetConfig(\Magento\Framework\View\Result\Page $subject, $result)
    {
        if ($this->registry->registry('amoptimizer_process') !== null) {
            $this->registry->unregister('amoptimizer_process');
        }

        $handles = $subject->getLayout()->getUpdate()->getHandles();

        $this->registry->register(
            'amoptimizer_process',
            !empty($handles) && is_array($handles) && in_array('default', $handles)
        );

        return $result;
    }
}
