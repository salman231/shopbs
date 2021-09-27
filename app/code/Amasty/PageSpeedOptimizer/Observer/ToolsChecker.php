<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_PageSpeedOptimizer
 */


namespace Amasty\PageSpeedOptimizer\Observer;

use Amasty\PageSpeedOptimizer\Model\ConfigProvider;
use Amasty\PageSpeedOptimizer\Model\OptionSource\GifOptimization;
use Amasty\PageSpeedOptimizer\Model\OptionSource\JpegOptimization;
use Amasty\PageSpeedOptimizer\Model\OptionSource\PngOptimization;
use Amasty\PageSpeedOptimizer\Model\OptionSource\WebpOptimization;
use Magento\Config\Model\ResourceModel\Config\Data\CollectionFactory;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Message\ManagerInterface;

/**
 * Class ToolsChecker cheeks if tools are installed
 *
 * @package Amasty\PageSpeedOptimizer\Observer
 */
class ToolsChecker implements ObserverInterface
{
    /**
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var CollectionFactory
     */
    private $configCollectionFactory;

    private $execChecked = false;

    public function __construct(
        ManagerInterface $messageManager,
        ConfigProvider $configProvider,
        CollectionFactory $configCollectionFactory
    ) {
        $this->messageManager = $messageManager;
        $this->configProvider = $configProvider;
        $this->configCollectionFactory = $configCollectionFactory;
    }

    /**
     * @inheritdoc
     */
    public function execute(Observer $observer)
    {
        $optimizeImages = $this->getConfigValue(
            ConfigProvider::OPTIMIZE_IMAGES,
            $observer->getWebsite(),
            $observer->getStore()
        );

        if (!$optimizeImages) {
            return;
        }

        $jpeg = $this->getConfigValue(ConfigProvider::JPEG_COMMAND, $observer->getWebsite(), $observer->getStore());
        if ($jpeg && $jpeg->getValue() != JpegOptimization::DO_NOT_OPTIMIZE) {
            $this->checkCommand(JpegOptimization::TOOLS[$jpeg->getValue()]);
        }

        $png = $this->getConfigValue(ConfigProvider::PNG_COMMAND, $observer->getWebsite(), $observer->getStore());
        if ($png && $png->getValue() != PngOptimization::DO_NOT_OPTIMIZE) {
            $this->checkCommand(PngOptimization::TOOLS[$png->getValue()]);
        }

        $gif = $this->getConfigValue(ConfigProvider::GIF_COMMAND, $observer->getWebsite(), $observer->getStore());
        if ($gif && $gif->getValue() != GifOptimization::DO_NOT_OPTIMIZE) {
            $this->checkCommand(GifOptimization::TOOLS[$gif->getValue()]);
        }

        $webP = $this->getConfigValue(ConfigProvider::WEBP, $observer->getWebsite(), $observer->getStore());
        if ($webP && $webP->getValue()) {
            $this->checkCommand(WebpOptimization::WEBP);
        }
    }

    /**
     * @param string $path
     * @param int $website
     * @param int $store
     *
     * @return bool|\Magento\Framework\DataObject
     */
    private function getConfigValue($path, $website = 0, $store = 0)
    {
        /** @var \Magento\Config\Model\ResourceModel\Config\Data\Collection $collection */
        $collection = $this->configCollectionFactory->create();
        if ($website) {
            $collection->addFieldToFilter('scope', \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITES);
            $collection->addFieldToFilter('scope_id', $website);
        } elseif ($store) {
            $collection->addFieldToFilter('scope', \Magento\Store\Model\ScopeInterface::SCOPE_STORES);
            $collection->addFieldToFilter('scope_id', $website);
        } else {
            $collection->addFieldToFilter('scope', 'default');
            $collection->addFieldToFilter('scope_id', 0);
        }

        $collection->addFieldToFilter('path', 'amoptimizer/' . $path);
        $collection->setPageSize(1)->setCurPage(1);

        if ($collection->getSize()) {
            return $collection->getFirstItem();
        }

        return false;
    }

    /**
     * @param $command
     *
     * @return void
     */
    private function checkCommand($command)
    {
        $disabled = explode(',', str_replace(' ', ',', ini_get('disable_functions')));
        if (in_array('exec', $disabled) && !$this->execChecked) {
            if (!$this->execChecked) {
                $this->execChecked = true;
                $this->messageManager->addWarningMessage(__('exec function is disabled.'));
            }

            return;
        }

        if (empty($command['check']) || empty($command['check']['command']) || empty($command['check']['result'])) {
            return;
        }

        $output = [];
        /** @codingStandardsIgnoreStart */
        exec($command['check']['command'] . ' 2>&1', $output);
        /** @codingStandardsIgnoreEnd */
        if (!empty($output)) {
            foreach ($output as $line) {
                if (false !== strpos($line, $command['check']['result'])) {
                    return;
                }
            }
        }

        $this->messageManager->addWarningMessage(__('Image Optimization Tool "%1" is not installed', $command['name']));
    }
}
