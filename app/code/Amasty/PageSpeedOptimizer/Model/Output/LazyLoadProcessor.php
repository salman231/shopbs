<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_PageSpeedOptimizer
 */


namespace Amasty\PageSpeedOptimizer\Model\Output;

use Amasty\PageSpeedOptimizer\Model\ConfigProvider;
use Amasty\PageSpeedOptimizer\Model\OptionSource\LazyLoadScript;
use Amasty\PageSpeedOptimizer\Model\OptionSource\PreloadStrategy;
use Amasty\PageSpeedOptimizer\Model\OptionSource\Resolutions;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\View\Asset\Repository;
use Magento\Framework\View\Layout;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class LazyLoadProcessor
 *
 * @package Amasty\PageSpeedOptimizer
 */
class LazyLoadProcessor implements OutputProcessorInterface
{
    const HOME = 'cms_index_index';
    const CATEGORY = 'catalog_category_view';
    const PRODUCT = 'catalog_product_view';
    const CMS = 'cms_page_view';
    const GENERAL = 'general';

    const PAGE_CONFIG = [
        self::HOME => 'lazy_load_home',
        self::CATEGORY => 'lazy_load_categories',
        self::PRODUCT => 'lazy_load_products',
        self::CMS => 'lazy_load_cms',
        self::GENERAL => 'lazy_load_general'
    ];

    /**
     * @var string
     */
    public $pageType;

    /**
     * @var string
     */
    private $storeMediaUrl;

    /**
     * @var \Magento\Framework\Filesystem\Directory\ReadInterface
     */
    private $mediaDirectory;

    /**
     * @var Layout
     */
    private $layout;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var Repository
     */
    private $assetRepo;

    public function __construct(
        Layout $layout,
        ConfigProvider $configProvider,
        Repository $assetRepo,
        Filesystem $filesystem,
        StoreManagerInterface $storeManager
    ) {
        $this->storeMediaUrl = $storeManager->getStore()->getBaseUrl(
            \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
        );
        $this->mediaDirectory = $filesystem->getDirectoryread(DirectoryList::MEDIA);
        $this->layout = $layout;
        $this->configProvider = $configProvider;
        $this->assetRepo = $assetRepo;
    }

    /**
     * @inheritdoc
     */
    public function process(&$output)
    {
        $this->detectPage($this->layout->getUpdate()->getHandles());
        if ($this->configProvider->isSimpleOptimization()) {
            $isLazy = $this->configProvider->isLazyLoad();
        } else {
            $isLazy = $this->configProvider->getConfig(
                self::PAGE_CONFIG[$this->pageType] . ConfigProvider::PART_IS_LAZY
            );
        }

        if ($isLazy) {
            if (false === strpos($output, '%lazy_before%')) {
                $output .= '%lazy_before%%lazy_after%';
            }
            $regExp = "<img(.*?)src=(\"|\'|)(.*?)(\"|\'| )(.*?)>";

            $skipImages = false;
            $skipStrategy = PreloadStrategy::SKIP_IMAGES;
            if (!$this->configProvider->isSimpleOptimization()) {
                $lazyScript = $this->configProvider->getConfig(
                    self::PAGE_CONFIG[$this->pageType] . ConfigProvider::PART_SCRIPT
                );
                $ignoreList = $this->configProvider->getConfig(
                    self::PAGE_CONFIG[$this->pageType] . ConfigProvider::PART_IGNORE
                );
                if (empty($ignoreList)) {
                    $ignoreList = [];
                } else {
                    $ignoreList = array_map('trim', explode(PHP_EOL, $ignoreList));
                }

                if ($this->configProvider->getConfig(
                    self::PAGE_CONFIG[$this->pageType] . ConfigProvider::PART_PRELOAD
                )) {
                    $skipImages = $this->configProvider->getConfig(
                        self::PAGE_CONFIG[$this->pageType] . ConfigProvider::PART_SKIP
                    );
                    $skipStrategy = $this->configProvider->getConfig(
                        self::PAGE_CONFIG[$this->pageType] . ConfigProvider::PART_STRATEGY
                    );
                }
            } else {
                $lazyScript = $this->configProvider->lazyLoadScript();
                $ignoreList = $this->configProvider->getIgnoreImages();
                if ($this->configProvider->isPreloadImages()) {
                    $skipImages = $this->configProvider->skipImagesCount();
                    $skipStrategy = $this->configProvider->getSkipStrategy();
                }
            }

            if ($skipImages === false) {
                $skipImages = 0;
            }

            $tempOutput = preg_replace('/<script.*?>.*?<\/script.*?>/is', '', $output);
            if (preg_match_all('/' . $regExp . '/is', $tempOutput, $images)) {
                $skipCounter = 1;
                foreach ($images[0] as $key => $image) {
                    $skip = false;
                    foreach ($ignoreList as $item) {
                        if (strpos($image, $item) !== false) {
                            $skip = true;
                            break;
                        }
                    }

                    if ($skip) {
                        continue;
                    }

                    if ($skipCounter < $skipImages) {
                        if ($skipStrategy == PreloadStrategy::SKIP_IMAGES) {
                            $skipCounter++;
                            continue;
                        }
                        $newImg = $this->replaceWithPictureTag($image, $images[3][$key]);
                        $output = str_replace($image, $newImg, $output);
                        $skipCounter++;
                        continue;
                    }

                    $replace = 'src=' . $images[2][$key] . $images[3][$key] . $images[4][$key];
                    $placeholder = 'src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABC'
                        . 'AQAAAC1HAwCAAAAC0lEQVR4nGP6zwAAAgcBApocMXEAAAAASUVORK5CYII="';
                    $newImg = str_replace($replace, $placeholder . ' data-am' . $replace, $image);

                    $output = str_replace($image, $newImg, $output);
                }
            }

            switch ($lazyScript) {
                case LazyLoadScript::NATIVE_LAZY:
                    $nativeLazy = '<script>' . \Amasty\PageSpeedOptimizer\Model\Js\NativeJsUglify::SCRIPT . '</script>';

                    $output = str_replace(['%lazy_before%', '%lazy_after%'], [$nativeLazy, ''], $output);
                    break;
                case LazyLoadScript::JQUERY_LAZY:
                default:
                    $jQLazy = '<script>
                        require(["jquery"], function (jquery) {
                            require(["Amasty_PageSpeedOptimizer/js/jquery.lazy"], function(lazy) {
                                if (document.readyState === "complete") {
                                    window.jQuery("img[data-amsrc]").lazy({"bind":"event", "attribute": "data-amsrc"});
                                } else {
                                    window.jQuery("img[data-amsrc]").lazy({"attribute": "data-amsrc"});
                                }
                            })
                        });
                    </script>';

                    $output = str_replace(['%lazy_before%', '%lazy_after%'], ['', $jQLazy], $output);
                    break;
            }
        } else {
            if ($this->configProvider->isSimpleOptimization()) {
                $isReplaceImages = $this->configProvider->isReplaceWithWebp();
            } else {
                $isReplaceImages = (bool)$this->configProvider->getConfig(
                    self::PAGE_CONFIG[$this->pageType] . ConfigProvider::PART_REPLACE_WITH_WEBP
                );
            }

            if ($isReplaceImages) {
                $this->replaceImages($output);
            }
        }

        $output = str_replace(['%lazy_before%', '%lazy_after%'], '', $output);
    }

    public function replaceWithPictureTag($image, $imagePath)
    {
        /** @codingStandardsIgnoreStart */
        $baseName = pathinfo($imagePath, PATHINFO_BASENAME);
        $extension = pathinfo($imagePath, PATHINFO_EXTENSION);

        if (strpos($imagePath, $this->storeMediaUrl) === false) {
            return $image;
        }
        $path = trim(str_replace($this->storeMediaUrl, '', $imagePath), '/');
        /** @codingStandardsIgnoreEnd */
        $webpName = str_replace('.' . $extension, '.webp', $baseName);

        $newImg = '<picture>';
        $replace = false;

        foreach (Resolutions::RESOLUTIONS as $data) {
            foreach (['image/webp' => $webpName, '' => $baseName] as $type => $fName) {
                $curPath = $data['dir'] . $path;

                if ($this->mediaDirectory->isExist(str_replace($baseName, $fName, $curPath))) {
                    $newImg .= '<source srcset="'
                        . str_replace($path, str_replace($baseName, $fName, $curPath), $imagePath) . '"';
                    if (!empty($data['width'])) {
                        $newImg .= 'media="(max-width: ' . $data['width'] . 'px)'
                            . (!empty($data['min-width']) ? 'and (min-width: ' . $data['min-width'] . 'px)' : '') . '"';
                    }
                    if (!empty($type)) {
                        $newImg .= 'type="' . $type . '"';
                    }
                    $newImg .= '>';
                    $replace = true;
                }
            }
        }

        if ($this->mediaDirectory->isExist(str_replace($baseName, $webpName, Resolutions::WEBP_DIR . $path))) {
            $newImg .= '<source srcset="'
                . str_replace(
                    $path,
                    str_replace($baseName, $webpName, Resolutions::WEBP_DIR . $path),
                    $imagePath
                ) . '" type="image/webp">';

            $replace = true;
        }

        if (!$replace) {
            return $image;
        }

        return $newImg . '<source srcset="' . $imagePath . '" >' . $image . '</picture>';
    }

    /**
     * @param string $output
     *
     * @return void
     */
    public function replaceImages(&$output)
    {
        $regExp = "<img(.*?)src=(\"|\'|)(.*?)(\"|\'| )(.*?)>";
        if (!$this->configProvider->isSimpleOptimization()) {
            $ignoreList = $this->configProvider->getConfig(
                self::PAGE_CONFIG[$this->pageType] . ConfigProvider::PART_REPLACE_IGNORE
            );

            if (empty($ignoreList)) {
                $ignoreList = [];
            } else {
                $ignoreList = array_map('trim', explode(PHP_EOL, $ignoreList));
            }
        } else {
            $ignoreList = $this->configProvider->getReplaceIgnoreList();
        }

        $tempOutput = preg_replace('/<script.*?>.*?<\/script.*?>/is', '', $output);
        if (preg_match_all('/' . $regExp . '/is', $tempOutput, $images)) {
            foreach ($images[0] as $key => $image) {
                $skip = false;
                foreach ($ignoreList as $item) {
                    if (strpos($image, $item) !== false) {
                        $skip = true;
                        break;
                    }
                }

                if ($skip) {
                    continue;
                }

                $newImg = $this->replaceWithPictureTag($image, $images[3][$key]);
                $output = str_replace($image, $newImg, $output);
            }
        }
    }

    /**
     * @param array $handles
     */
    public function detectPage($handles = [])
    {
        if (in_array(self::HOME, $handles)) {
            $this->pageType = self::HOME;
        } elseif (in_array(self::CMS, $handles)) {
            $this->pageType = self::CMS;
        } elseif (in_array(self::CATEGORY, $handles)) {
            $this->pageType = self::CATEGORY;
        } elseif (in_array(self::PRODUCT, $handles)) {
            $this->pageType = self::PRODUCT;
        } else {
            $this->pageType = self::GENERAL;
        }
    }
}
