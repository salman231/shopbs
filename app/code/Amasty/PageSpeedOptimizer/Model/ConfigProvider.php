<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_PageSpeedOptimizer
 */


namespace Amasty\PageSpeedOptimizer\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Class ConfigProvider for getting module settings easily
 *
 * @package Amasty\PageSpeedOptimizer
 */
class ConfigProvider extends \Amasty\Base\Model\ConfigProviderAbstract
{
    protected $pathPrefix = 'amoptimizer/';

    /**#@+
     * Constants defined for xpath of system configuration
     */
    const XPATH_ENABLED = 'general/enabled';
    const MOVE_JS = 'settings/javascript/movejs';
    const MOVE_PRINT_CSS = 'css/move_print';
    const MOVE_FONT = 'settings/css/move_font';
    const FONT_IGNORE_LIST = 'settings/css/font_ignore_list';
    const LAZY_LOAD = 'images/lazy_load';
    const LAZY_LOAD_SCRIPT = 'images/lazy_load_script';
    const PRELOAD_IMAGES = 'images/preload_images';
    const SKIP_IMAGES_COUNT = 'images/skip_images_count';
    const OPTIMIZE_IMAGES = 'images/optimize_images';
    const OPTIMIZE_AUTOMATICALLY = 'images/optimize_automatically';
    const REPLACE_WITH_WEBP = 'images/webp_resolutions';
    const REPLACE_IGNORE_IMAGES = 'images/webp_resolutions_ignore';
    const FOLDERS = 'images/folders';
    const JPEG_COMMAND = 'images/jpeg_tool';
    const PNG_COMMAND = 'images/png_tool';
    const GIF_COMMAND = 'images/gif_tool';
    const DUMP_ORIGINAL = 'images/dump_original';
    const IGNORE_IMAGES = 'images/ignore_list';
    const RESOLUTIONS = 'images/resolutions';
    const RESIZE_ALGORITHM = 'images/resize_algorithm';
    const WEBP = 'images/webp';
    const SKIP_STRATEGY = 'images/preload_images_strategy';
    const IMAGE_OPTIMIZATION_TYPE = 'images/image_optimization_type';
    /**#@-*/

    const PART_IS_LAZY = '/lazy_load';
    const PART_SCRIPT = '/lazy_load_script';
    const PART_STRATEGY = '/preload_images_strategy';
    const PART_PRELOAD = '/preload_images';
    const PART_SKIP = '/skip_images_count';
    const PART_IGNORE = '/ignore_list';
    const PART_REPLACE_WITH_WEBP = '/webp_resolutions';
    const PART_REPLACE_IGNORE = '/webp_resolutions_ignore';
    const BUNDLING_TYPE = 'javascript/bundling_type';
    const BUNDLE_STEP = 'javascript/bundle_step';
    const BUNDLE_HASH = 'javascript/bundle_hash';

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->isSetFlag(self::XPATH_ENABLED);
    }

    /**
     * @return bool
     */
    public function isMoveJS()
    {
        return $this->isSetFlag(self::MOVE_JS);
    }

    public function isMovePrintCss()
    {
        return $this->isSetFlag(self::MOVE_PRINT_CSS);
    }

    /**
     * @return bool
     */
    public function isLazyLoad()
    {
        return $this->isSetFlag(self::LAZY_LOAD);
    }

    /**
     * @return int
     */
    public function lazyLoadScript()
    {
        return (int)$this->getValue(self::LAZY_LOAD_SCRIPT);
    }

    /**
     * @return bool
     */
    public function isOptimizeImages()
    {
        return (bool)$this->getValue(self::OPTIMIZE_IMAGES);
    }

    /**
     * @return bool
     */
    public function isAutomaticallyOptimizeImages()
    {
        return (bool)$this->getValue(self::OPTIMIZE_AUTOMATICALLY);
    }

    /**
     * @return int
     */
    public function getJpegCommand()
    {
        return (int)$this->getValue(self::JPEG_COMMAND, 0, ScopeConfigInterface::SCOPE_TYPE_DEFAULT);
    }

    /**
     * @return int
     */
    public function getPngCommand()
    {
        return (int)$this->getValue(self::PNG_COMMAND, 0, ScopeConfigInterface::SCOPE_TYPE_DEFAULT);
    }

    /**
     * @return int
     */
    public function getGifCommand()
    {
        return (int)$this->getValue(self::GIF_COMMAND, 0, ScopeConfigInterface::SCOPE_TYPE_DEFAULT);
    }

    /**
     * @return bool
     */
    public function isDumpOriginal()
    {
        return $this->isSetFlag(self::DUMP_ORIGINAL);
    }

    /**
     * @return bool
     */
    public function isMoveFont()
    {
        return $this->isSetFlag(self::MOVE_FONT);
    }

    /**
     * @return array
     */
    public function getFontIgnoreList()
    {
        $ignoreList = $this->getValue(self::FONT_IGNORE_LIST);
        if (empty($ignoreList)) {
            return [];
        }

        return array_map('trim', explode(PHP_EOL, $ignoreList));
    }

    /**
     * @return bool
     */
    public function isPreloadImages()
    {
        return $this->isSetFlag(self::PRELOAD_IMAGES);
    }

    /**
     * @return int
     */
    public function skipImagesCount()
    {
        return (int)$this->getValue(self::SKIP_IMAGES_COUNT);
    }

    /**
     * @return array
     */
    public function getIgnoreImages()
    {
        $ignoreList = $this->getValue(self::IGNORE_IMAGES);
        if (empty($ignoreList)) {
            return [];
        }

        return array_map('trim', explode(PHP_EOL, $ignoreList));
    }

    /**
     * @return array|bool
     */
    public function getResolutions()
    {
        if ($this->getValue(self::RESOLUTIONS) !== '') {
            return explode(',', $this->getValue(self::RESOLUTIONS));
        }

        return false;
    }

    /**
     * @return bool
     */
    public function isCreateWebp()
    {
        return $this->isSetFlag(self::WEBP);
    }

    /**
     * @return bool
     */
    public function isReplaceWithWebP()
    {
        return (bool)$this->isSetFlag(self::REPLACE_WITH_WEBP);
    }

    /**
     * @return array
     */
    public function getReplaceIgnoreList()
    {
        $ignoreList = $this->getValue(self::REPLACE_IGNORE_IMAGES);
        if (empty($ignoreList)) {
            return [];
        }

        return array_map('trim', explode(PHP_EOL, $ignoreList));
    }

    /**
     * @return bool
     */
    public function isSimpleOptimization()
    {
        return (int)$this->getValue(self::IMAGE_OPTIMIZATION_TYPE) === OptionSource\OptimizationSettings::SIMPLE;
    }

    public function getSkipStrategy()
    {
        return (int)$this->getValue(self::SKIP_STRATEGY);
    }

    public function getConfig($path)
    {
        return $this->getValue($path);
    }

    public function getResizeAlgorithm()
    {
        return (int)$this->getValue(self::RESIZE_ALGORITHM);
    }

    public function getFolders()
    {
        $folders = $this->getValue(self::FOLDERS);
        if (!empty($folders)) {
            return explode(',', $folders);
        }

        return [];
    }

    public function getBundlingType()
    {
        return (int)$this->getValue(self::BUNDLING_TYPE);
    }

    public function getBundleStep()
    {
        return (int)$this->getValue(self::BUNDLE_STEP);
    }

    public function getBundleHash()
    {
        return $this->getValue(self::BUNDLE_HASH);
    }
}
