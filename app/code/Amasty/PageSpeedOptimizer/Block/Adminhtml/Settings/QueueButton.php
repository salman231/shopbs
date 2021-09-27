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
 * Class QueueButton
 *
 * @package Amasty\PageSpeedOptimizer
 */
class QueueButton extends Field
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
            $element->setData('value', __("Generate Queue"));
            $element->setData('class', "action-default");
        } else {
            $element->setData('value', __("ReGenerate Queue"));
            $element->setData('class', "action-default");
        }

        $block = $this->getLayout()
            ->createBlock(\Magento\Backend\Block\Template::class)
            ->setTemplate('Amasty_PageSpeedOptimizer::generate_queue.phtml');

        return parent::_getElementHtml($element) . $block->toHtml();
    }
}
