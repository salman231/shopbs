<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_PageSpeedOptimizer
 */

namespace Amasty\PageSpeedOptimizer\Block\Adminhtml\Settings;

use Amasty\PageSpeedOptimizer\Api\QueueRepositoryInterface;
use Magento\Backend\Block\Template;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

/**
 * Class ForceButton creates button for image optimization via admin area
 *
 * @package Amasty\PageSpeedOptimizer
 */
class ForceButton extends Field
{
    /**
     * @var QueueRepositoryInterface
     */
    private $queueRepository;

    public function __construct(
        QueueRepositoryInterface $queueRepository,
        Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->queueRepository = $queueRepository;
    }

    /**
     * @param AbstractElement $element
     *
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        if ($this->queueRepository->isQueueEmpty()) {
            $element->setData('readonly', true);
        }

        $element->setData('value', __("Run Optimization now"));
        $element->setData('class', "action-default");

        $block = $this->getLayout()
            ->createBlock(\Magento\Backend\Block\Template::class)
            ->setTemplate('Amasty_PageSpeedOptimizer::force_optimize.phtml')
            ->setProcessUrl($this->getActionUrl())
            ->setImagesCount($this->queueRepository->getQueueSize());

        return parent::_getElementHtml($element) . $block->toHtml();
    }

    /**
     * @return string
     */
    public function getActionUrl()
    {
        return $this->_urlBuilder->getUrl('amoptimizer/image/run');
    }
}
