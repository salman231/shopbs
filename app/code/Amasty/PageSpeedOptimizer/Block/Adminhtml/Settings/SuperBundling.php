<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_PageSpeedOptimizer
 */

namespace Amasty\PageSpeedOptimizer\Block\Adminhtml\Settings;

use Amasty\PageSpeedOptimizer\Model\ConfigProvider;
use Magento\Backend\Block\Template;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Url;

/**
 * Class SuperBundling creates steps for advanced bundling process
 *
 * @package Amasty\PageSpeedOptimizer
 */
class SuperBundling extends Field
{
    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var Url
     */
    private $url;

    public function __construct(
        ConfigProvider $configProvider,
        Url $url,
        Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->configProvider = $configProvider;
        $this->url = $url;
    }

    /**
     * @param AbstractElement $element
     *
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        switch ($this->configProvider->getBundleStep()) {
            case 1:
                $element->setData('value', __("Next"));
                $element->setData('class', "action-default");

                $block = $this->getLayout()
                    ->createBlock(\Magento\Backend\Block\Template::class)
                    ->setTemplate('Amasty_PageSpeedOptimizer::bundling_step1.phtml');

                return $block->toHtml() . '<div>' . parent::_getElementHtml($element) . '</div>';
            case 2:
                $element->setData('value', __("Next"));
                $element->setData('class', "action-default");

                $block = $this->getLayout()
                    ->createBlock(\Magento\Backend\Block\Template::class)
                    ->setTemplate('Amasty_PageSpeedOptimizer::bundling_step2.phtml')
                    ->setUrls($this->getUrls());

                return $block->toHtml() . '<div>' . parent::_getElementHtml($element) . '</div>';
            case 3:
                $element->setData('value', __("Finish"));
                $element->setData('class', "action-default");

                $block = $this->getLayout()
                    ->createBlock(\Magento\Backend\Block\Template::class)
                    ->setTemplate('Amasty_PageSpeedOptimizer::bundling_step3.phtml');

                return $block->toHtml() . '<div>' . parent::_getElementHtml($element) . '</div>';
            default:
                $element->setData('value', __("Start"));
                $element->setData('class', "action-default");

                $block = $this->getLayout()
                    ->createBlock(\Magento\Backend\Block\Template::class)
                    ->setTemplate('Amasty_PageSpeedOptimizer::super_bundling_button.phtml');

                return parent::_getElementHtml($element) . $block->toHtml();
        }
    }

    public function getUrls()
    {
        $bundleHash = $this->configProvider->getBundleHash();
        $result = [];
        foreach ($this->_storeManager->getStores() as $store) {
            $result[$store->getBaseUrl()] = $store->getBaseUrl() . '?amoptimizer_bundle_check=' . $bundleHash;
        }
        return $result;
    }
}
