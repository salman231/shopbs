<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_PageSpeedOptimizer
 */


namespace Amasty\PageSpeedOptimizer\Plugin;

use Amasty\PageSpeedOptimizer\Api\Data\BundleFileInterface;
use Amasty\PageSpeedOptimizer\Model\Bundle\ResourceModel\CollectionFactory;
use Amasty\PageSpeedOptimizer\Model\ConfigProvider;
use Magento\Framework\Registry;

/**
 * Class Deploy collects files for bundling
 * previously saved in \Amasty\PageSpeedOptimizer\Controller\Bundle\Modules
 *
 * @package Amasty\PageSpeedOptimizer
 */
class Deploy
{
    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    public function __construct(
        ConfigProvider $configProvider,
        CollectionFactory $collectionFactory,
        Registry $registry
    ) {
        $this->configProvider = $configProvider;
        $this->registry = $registry;
        $this->collectionFactory = $collectionFactory;
    }

    public function beforeDeploy($subject, $area, $theme, $locale)
    {
        /** @var \Amasty\PageSpeedOptimizer\Model\Bundle\ResourceModel\Collection $collection */
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter('main_table.' . BundleFileInterface::AREA, $area);
        $collection->addFieldToFilter('main_table.' . BundleFileInterface::THEME, $theme);
        $collection->addFieldToFilter('main_table.' . BundleFileInterface::LOCALE, $locale);
        $collection->addFieldToSelect(BundleFileInterface::FILENAME);
        $result = $collection->getData();
        if (empty($result)) {
            $result = false;
        } else {
            foreach ($result as &$item) {
                $item = $item[BundleFileInterface::FILENAME];
            }
        }
        $this->registry->register('am_bundle_files', $result);
    }

    public function afterDeploy()
    {
        $this->registry->unregister('am_bundle_files');
    }
}
