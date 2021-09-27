<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_PageSpeedOptimizer
 */


namespace Amasty\PageSpeedOptimizer\Plugin;

use Amasty\PageSpeedOptimizer\Model\ConfigProvider;
use Amasty\PageSpeedOptimizer\Model\Image\Process;
use Amasty\PageSpeedOptimizer\Model\OptionSource\GifOptimization;
use Amasty\PageSpeedOptimizer\Model\OptionSource\JpegOptimization;
use Amasty\PageSpeedOptimizer\Model\OptionSource\PngOptimization;
use Amasty\PageSpeedOptimizer\Model\Queue\QueueFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\ReadInterface;

/**
 * Class AbstractImage common class for AdapterImage and WysiwigImage
 *
 * @package Amasty\PageSpeedOptimizer
 */
class AbstractImage
{
    /**
     * @var ConfigProvider
     */
    protected $configProvider;

    /**
     * @var Process
     */
    protected $imageProcessor;

    /**
     * @var QueueFactory
     */
    protected $queueFactory;

    /**
     * @var ReadInterface
     */
    private $mediaDirectory;

    public function __construct(
        ConfigProvider $configProvider,
        Process $imageProcessor,
        QueueFactory $queueFactory,
        Filesystem $filesystem
    ) {
        $this->configProvider = $configProvider;
        $this->imageProcessor = $imageProcessor;
        $this->queueFactory = $queueFactory;
        $this->mediaDirectory = $filesystem->getDirectoryRead(DirectoryList::MEDIA);
    }

    /**
     * @param string $filename
     *
     * @return \Amasty\PageSpeedOptimizer\Api\Data\QueueInterface|bool
     */
    public function prepareFile($filename)
    {
        /** @codingStandardsIgnoreStart */
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        /** @codingStandardsIgnoreEnd */

        $skipJpeg = $this->configProvider->getJpegCommand() === JpegOptimization::DO_NOT_OPTIMIZE;
        $skipPng = $this->configProvider->getPngCommand() === PngOptimization::DO_NOT_OPTIMIZE;
        $skipGif = $this->configProvider->getGifCommand() === GifOptimization::DO_NOT_OPTIMIZE;
        $resizeAlgorithm = $this->configProvider->getResizeAlgorithm();

        switch ($ext) {
            case 'jpg':
            case 'jpeg':
                if ($skipJpeg) {
                    return false;
                }
                break;
            case 'png':
                if ($skipPng) {
                    return false;
                }
                break;
            case 'gif':
                if ($skipGif) {
                    return false;
                }
                break;
            default:
                return false;
        }

        $webp = $this->configProvider->isCreateWebp();
        $isDumpOriginal = $this->configProvider->isDumpOriginal();
        $resolutions = $this->configProvider->getResolutions();
        /** @var \Amasty\PageSpeedOptimizer\Api\Data\QueueInterface $queue */
        $queue = $this->queueFactory->create();
        $queue->setFilename($this->mediaDirectory->getRelativePath($filename))
            ->setExtension($ext)
            ->setResolutions($resolutions)
            ->setIsUseWebP($webp)
            ->setIsDumpOriginal($isDumpOriginal)
            ->setResizeAlgorithm($resizeAlgorithm);

        return $queue;
    }
}
