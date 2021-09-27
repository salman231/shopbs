<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_PageSpeedOptimizer
 */


namespace Amasty\PageSpeedOptimizer\Plugin;

use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Class MoveFont excludes fonts from merged css files and puts them into another file
 *
 * @package Amasty\PageSpeedOptimizer
 */
class MoveFont
{
    /**
     * @var string
     */
    private $filePath;

    /**
     * @var \Amasty\PageSpeedOptimizer\Model\ConfigProvider
     */
    private $configProvider;

    /**
     * @var \Magento\Framework\Filesystem
     */
    private $filesystem;

    public function __construct(
        \Amasty\PageSpeedOptimizer\Model\ConfigProvider $configProvider,
        \Magento\Framework\Filesystem $filesystem
    ) {
        $this->configProvider = $configProvider;
        $this->filesystem = $filesystem;
    }

    public function beforeMerge($subject, $assetsToMerge, $resultAsset)
    {
        if ($this->configProvider->isEnabled()
            && $this->configProvider->isMoveFont() && $resultAsset->getContentType() === 'css'
        ) {
            $this->filePath = $resultAsset->getPath();
        }
    }

    public function afterMerge($subject)
    {
        if ($this->configProvider->isEnabled() && $this->configProvider->isMoveFont() && $this->filePath) {
            $staticDir = $this->filesystem->getDirectoryWrite(DirectoryList::STATIC_VIEW);
            $mergedContent = $staticDir->readFile($this->filePath);
            $fonts = [];
            $fontIgnoreList = $this->configProvider->getFontIgnoreList();
            $mergedContent = preg_replace_callback(
                '/@font-face\s*\{.*?\}/is',
                function ($match) use (&$fonts, $fontIgnoreList) {
                    foreach ($fontIgnoreList as $ignoreFont) {
                        if (strpos($match[0], $ignoreFont) !== false) {
                            return $match[0];
                        }
                    }
                    $fonts[] = $match[0];
                    return '';
                },
                $mergedContent
            );
            if (!empty($fonts)) {
                $fontsPath = str_replace(
                    $this->basename($this->filePath),
                    'fonts_' . $this->basename($this->filePath),
                    $this->filePath
                );
                $staticDir->writeFile($fontsPath, implode('', $fonts));
                $staticDir->writeFile($this->filePath, $mergedContent);
            }
        }
    }

    /**
     * @param string $file
     *
     * @return string
     */
    private function basename($file)
    {
        /** @codingStandardsIgnoreStart */
        $basename = basename($file);
        /** @codingStandardsIgnoreEnd */

        return $basename;
    }
}
