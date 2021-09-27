<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_PageSpeedOptimizer
 */


namespace Amasty\PageSpeedOptimizer\Model\Image;

use Amasty\PageSpeedOptimizer\Model\OptionSource\GifOptimization;
use Amasty\PageSpeedOptimizer\Model\OptionSource\JpegOptimization;
use Amasty\PageSpeedOptimizer\Model\OptionSource\PngOptimization;
use Amasty\PageSpeedOptimizer\Model\OptionSource\Resolutions;
use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Class GenerateQueue
 *
 * @package Amasty\PageSpeedOptimizer
 */
class GenerateQueue
{
    /**
     * @var \Amasty\PageSpeedOptimizer\Model\Queue\QueueRepository
     */
    private $queueRepository;

    /**
     * @var \Magento\Framework\Filesystem\Directory\ReadInterface
     */
    private $mediaDirectory;

    /**
     * @var \Amasty\PageSpeedOptimizer\Model\ConfigProvider
     */
    private $configProvider;

    /**
     * @var \Amasty\PageSpeedOptimizer\Model\Queue\QueueFactory
     */
    private $queueFactory;

    /**
     * @var \Magento\Framework\App\Cache\TypeListInterface
     */
    private $cache;

    public function __construct(
        \Amasty\PageSpeedOptimizer\Api\QueueRepositoryInterface $queueRepository,
        \Amasty\PageSpeedOptimizer\Model\Queue\QueueFactory $queueFactory,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\App\Cache\TypeListInterface $cache,
        \Amasty\PageSpeedOptimizer\Model\ConfigProvider $configProvider
    ) {
        $this->queueRepository = $queueRepository;
        $this->mediaDirectory = $filesystem->getDirectoryRead(DirectoryList::MEDIA);
        $this->configProvider = $configProvider;
        $this->queueFactory = $queueFactory;
        $this->cache = $cache;
    }

    /**
     * @return int
     */
    public function generateQueue()
    {
        $this->cache->cleanType(\Magento\Framework\App\Cache\Type\Config::TYPE_IDENTIFIER);
        $this->queueRepository->clearQueue();
        $this->processFiles();

        return $this->queueRepository->getQueueSize();
    }

    /**
     * @return void
     */
    public function processFiles()
    {
        $webp = $this->configProvider->isCreateWebp();
        $isDumpOriginal = $this->configProvider->isDumpOriginal();
        $resolutions = $this->configProvider->getResolutions();
        $skipJpeg = $this->configProvider->getJpegCommand() === JpegOptimization::DO_NOT_OPTIMIZE;
        $skipPng = $this->configProvider->getPngCommand() === PngOptimization::DO_NOT_OPTIMIZE;
        $skipGif = $this->configProvider->getGifCommand() === GifOptimization::DO_NOT_OPTIMIZE;
        $resizeAlgorithm = $this->configProvider->getResizeAlgorithm();

        foreach ($this->configProvider->getFolders() as $imageDirectory) {
            $files = $this->mediaDirectory->readRecursively($imageDirectory);
            foreach ($files as $file) {
                $skip = false;
                foreach (Resolutions::RESOLUTIONS as $resolution) {
                    if (strpos($file, $resolution['dir']) !== false) {
                        $skip = true;
                    }
                }
                if (!$skip && strpos($file, Process::DUMP_DIRECTORY) === false
                    && $this->mediaDirectory->isFile($file)
                ) {
                    /** @codingStandardsIgnoreStart */
                    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                    /** @codingStandardsIgnoreEnd */
                    switch ($ext) {
                        case 'jpg':
                        case 'jpeg':
                            $skip = $skipJpeg && !$webp;
                            break;
                        case 'png':
                            $skip = $skipPng && !$webp;
                            break;
                        case 'gif':
                            $skip = $skipGif && !$webp;
                            break;
                        default:
                            $skip = true;
                    }
                    if ($skip) {
                        continue;
                    }

                    /** @var \Amasty\PageSpeedOptimizer\Api\Data\QueueInterface $queue */
                    $queue = $this->queueFactory->create();
                    $queue->setFilename($file)
                        ->setExtension($ext)
                        ->setResolutions($resolutions)
                        ->setIsUseWebP($webp)
                        ->setIsDumpOriginal($isDumpOriginal)
                        ->setResizeAlgorithm($resizeAlgorithm);

                    $this->queueRepository->addToQueue($queue);
                }
            }
        }
    }
}
