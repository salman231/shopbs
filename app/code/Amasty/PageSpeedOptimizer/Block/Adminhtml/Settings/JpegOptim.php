<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_PageSpeedOptimizer
 */

namespace Amasty\PageSpeedOptimizer\Block\Adminhtml\Settings;

use Magento\Framework\Data\Form\Element\AbstractElement;

/**
 * Class JpegOptim
 *
 * @package Amasty\PageSpeedOptimizer
 */
class JpegOptim extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * @param AbstractElement $element
     *
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function _getElementHtml(AbstractElement $element)
    {
        $block = $this->getLayout()
            ->createBlock(\Magento\Backend\Block\Template::class)
            ->setTemplate('Amasty_PageSpeedOptimizer::jpegoptim.phtml');

        return $block->toHtml();
    }
}
