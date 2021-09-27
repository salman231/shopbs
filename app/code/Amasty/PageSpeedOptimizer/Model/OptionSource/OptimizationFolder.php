<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_PageSpeedOptimizer
 */


namespace Amasty\PageSpeedOptimizer\Model\OptionSource;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Option\ArrayInterface;

/**
 * Class OptimizationFolder
 *
 * @package Amasty\PageSpeedOptimizer
 */
class OptimizationFolder implements ArrayInterface
{
    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    private $mediaDirectory;

    public function __construct(
        \Magento\Framework\Filesystem $filesystem
    ) {
        $this->mediaDirectory = $filesystem->getDirectoryRead(DirectoryList::MEDIA);
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $optionArray = [];
        foreach ($this->toArray() as $widgetType => $label) {
            $optionArray[] = ['value' => $widgetType, 'label' => $label];
        }
        return $optionArray;
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        $result = [];
        $folders = $this->mediaDirectory->read('.');
        foreach ($folders as $folder) {
            if ($this->mediaDirectory->isDirectory($folder)) {
                $folder = preg_replace('/^\.\/(.*)/is', '$1', $folder);
                $result[$folder] = $folder;
            }
        }

        return $result;
    }
}
