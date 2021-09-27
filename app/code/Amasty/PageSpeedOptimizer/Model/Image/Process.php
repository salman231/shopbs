<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_PageSpeedOptimizer
 */


namespace Amasty\PageSpeedOptimizer\Model\Image;

use Amasty\PageSpeedOptimizer\Api\Data\QueueInterface;
use Amasty\PageSpeedOptimizer\Model\OptionSource\GifOptimization;
use Amasty\PageSpeedOptimizer\Model\OptionSource\JpegOptimization;
use Amasty\PageSpeedOptimizer\Model\OptionSource\PngOptimization;
use Amasty\PageSpeedOptimizer\Model\OptionSource\ResizeAlgorithm;
use Amasty\PageSpeedOptimizer\Model\OptionSource\Resolutions;
use Amasty\PageSpeedOptimizer\Model\OptionSource\WebpOptimization;
use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Class Process
 *
 * @package Amasty\PageSpeedOptimizer
 */
class Process
{
    const DUMP_DIRECTORY = 'amasty' . DIRECTORY_SEPARATOR . 'amoptimizer_dump' . DIRECTORY_SEPARATOR;

    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    private $mediaDirectory;

    /**
     * @var \Amasty\PageSpeedOptimizer\Model\ConfigProvider
     */
    private $configProvider;

    /**
     * @var \Magento\Framework\Image\Adapter\Gd2
     */
    private $gd2;

    /**
     * @var \Magento\Framework\Shell
     */
    private $shell;

    /**
     * @var bool
     */
    private $skipJpeg;

    /**
     * @var bool
     */
    private $skipPng;

    /**
     * @var bool
     */
    private $skipGif;

    public function __construct(
        \Amasty\PageSpeedOptimizer\Model\ConfigProvider $configProvider,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Image\Adapter\Gd2 $gd2,
        \Magento\Framework\Shell $shell
    ) {
        $this->mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->configProvider = $configProvider;
        $this->gd2 = $gd2;
        $this->shell = $shell;
        $this->skipJpeg = $this->configProvider->getJpegCommand() == JpegOptimization::DO_NOT_OPTIMIZE;
        $this->skipPng = $this->configProvider->getPngCommand() == PngOptimization::DO_NOT_OPTIMIZE;
        $this->skipGif = $this->configProvider->getGifCommand() == GifOptimization::DO_NOT_OPTIMIZE;
    }

    /**
     * @param QueueInterface $queue
     *
     * @return array
     */
    public function execute($queue)
    {
        $output = [];

        if (!$this->mediaDirectory->isExist($queue->getFilename())) {
            return $output;
        }

        if ($this->mediaDirectory->isExist(self::DUMP_DIRECTORY . $queue->getFilename())) {
            $this->mediaDirectory->copyFile(self::DUMP_DIRECTORY . $queue->getFilename(), $queue->getFilename());
        }

        switch ($queue->getExtension()) {
            case 'jpg':
            case 'jpeg':
                $this->processJpeg($queue);
                break;
            case 'png':
                $this->processPng($queue);
                break;
            case 'gif':
                $this->processGif($queue);
                break;
        }

        return $output;
    }

    public function processJpeg(QueueInterface $queue)
    {
        $imagePath = $this->mediaDirectory->getAbsolutePath($queue->getFilename());

        if ($queue->getResolutions()) {
            $command = false;
            if (!$this->skipJpeg) {
                $command = JpegOptimization::TOOLS[$this->configProvider->getJpegCommand()]['command'];
            }
            $this->processResolutions(
                $queue,
                $command
            );
        }
        if ($queue->isUseWebP()) {
            $webPPath = str_replace(
                $queue->getFilename(),
                Resolutions::WEBP_DIR . $queue->getFilename(),
                $imagePath
            );
            if (!$this->mediaDirectory->isExist($this->dirname($webPPath))) {
                $this->mediaDirectory->create($this->dirname($webPPath));
            }
            $webP = str_replace('.' . $queue->getExtension(), '.webp', $webPPath);
            try {
                $this->shell->execute(
                    str_replace(
                        [
                            '%f',
                            '%o'
                        ],
                        [
                            $imagePath,
                            $webP
                        ],
                        WebpOptimization::WEBP['command']
                    )
                );
            } catch (\Exception $e) {
                null;
            }
        }
        if ($queue->isDumpOriginal()) {
            $this->dumpOriginalImage($queue->getFilename());
        }

        if (!$this->skipJpeg) {
            try {
                $this->shell->execute(
                    str_replace(
                        '%f',
                        $imagePath,
                        JpegOptimization::TOOLS[$this->configProvider->getJpegCommand()]['command']
                    )
                );
            } catch (\Exception $e) {
                null;
            }
        }
    }

    public function processPng(QueueInterface $queue)
    {
        $imagePath = $this->mediaDirectory->getAbsolutePath($queue->getFilename());

        if ($queue->getResolutions()) {
            $command = false;
            if (!$this->skipPng) {
                $command = PngOptimization::TOOLS[$this->configProvider->getPngCommand()]['command'];
            }
            $this->processResolutions(
                $queue,
                $command
            );
        }
        if ($queue->isUseWebP()) {
            $webPPath = str_replace(
                $queue->getFilename(),
                Resolutions::WEBP_DIR . $queue->getFilename(),
                $imagePath
            );
            if (!$this->mediaDirectory->isExist($this->dirname($webPPath))) {
                $this->mediaDirectory->create($this->dirname($webPPath));
            }
            $webP = str_replace('.' . $queue->getExtension(), '.webp', $webPPath);
            try {
                $this->shell->execute(
                    str_replace(
                        [
                            '%f',
                            '%o'
                        ],
                        [
                            $imagePath,
                            $webP
                        ],
                        WebpOptimization::WEBP['command']
                    )
                );
            } catch (\Exception $e) {
                null;
            }
        }
        if ($queue->isDumpOriginal()) {
            $this->dumpOriginalImage($queue->getFilename());
        }

        if (!$this->skipPng) {
            try {
                $this->shell->execute(
                    str_replace(
                        '%f',
                        $imagePath,
                        PngOptimization::TOOLS[$this->configProvider->getPngCommand()]['command']
                    )
                );
            } catch (\Exception $e) {
                null;
            }
        }
    }

    public function processGif(QueueInterface $queue)
    {
        $imagePath = $this->mediaDirectory->getAbsolutePath($queue->getFilename());

        if ($queue->isUseWebP()) {
            $webPPath = str_replace(
                $queue->getFilename(),
                Resolutions::WEBP_DIR . $queue->getFilename(),
                $imagePath
            );
            if (!$this->mediaDirectory->isExist($this->dirname($webPPath))) {
                $this->mediaDirectory->create($this->dirname($webPPath));
            }
            $webP = str_replace('.' . $queue->getExtension(), '.webp', $webPPath);
            try {
                $this->shell->execute(
                    str_replace(
                        [
                            '%f',
                            '%o'
                        ],
                        [
                            $imagePath,
                            $webP
                        ],
                        WebpOptimization::WEBP['command']
                    )
                );
            } catch (\Exception $e) {
                null;
            }
        }

        if ($queue->isDumpOriginal()) {
            $this->dumpOriginalImage($queue->getFilename());
        }

        if (!$this->skipGif) {
            try {
                $this->shell->execute(
                    str_replace(
                        '%f',
                        $imagePath,
                        GifOptimization::TOOLS[$this->configProvider->getGifCommand()]['command']
                    )
                );
            } catch (\Exception $e) {
                null;
            }
        }
    }

    public function processResolutions(QueueInterface $queue, $command)
    {
        $imagePath = $this->mediaDirectory->getAbsolutePath($queue->getFilename());
        $resolutions = $queue->getResolutions();

        try {
            $this->gd2->open($imagePath);
        } catch (\Exception $e) {
            return;
        }

        $width = $this->gd2->getOriginalWidth();
        $height = $this->gd2->getOriginalHeight();
        if ($width == 0 || $height == 0) {
            return;
        }
        $this->gd2->keepAspectRatio(true);

        foreach (Resolutions::RESOLUTIONS as $resolutionKey => $resolutionData) {
            if (in_array($resolutionKey, $resolutions) && $width > $resolutionData['width']) {
                switch ($queue->getResizeAlgorithm()) {
                    case ResizeAlgorithm::RESIZE:
                        try {
                            $this->gd2->resize($resolutionData['width']);
                        } catch (\Exception $e) {
                            continue;
                        }
                        break;
                    case ResizeAlgorithm::CROP:
                        try {
                            $this->gd2->crop(0, 0, $width - $resolutionData['width'], 0);
                        } catch (\Exception $e) {
                            continue;
                        }
                        break;
                }

                $newName = str_replace(
                    $queue->getFilename(),
                    $resolutionData['dir'] . $queue->getFilename(),
                    $imagePath
                );
                if (!$this->mediaDirectory->isExist($this->dirname($newName))) {
                    $this->mediaDirectory->create($this->dirname($newName));
                }
                $this->gd2->save($newName);

                if ($queue->isUseWebP()) {
                    $webP = str_replace('.' .  $queue->getExtension(), '.webp', $newName);
                    try {
                        $this->shell->execute(
                            str_replace(
                                [
                                    '%f',
                                    '%o'
                                ],
                                [
                                    $newName,
                                    $webP
                                ],
                                WebpOptimization::WEBP['command']
                            )
                        );
                    } catch (\Exception $e) {
                        null;
                    }
                }

                if ($command) {
                    try {
                        $this->shell->execute(
                            str_replace(
                                '%f',
                                $newName,
                                $command
                            )
                        );
                    } catch (\Exception $e) {
                        null;
                    }
                }

                $this->gd2->open($imagePath);
            }
        }
    }

    public function dumpOriginalImage($imagePath)
    {
        $dumpImagePath = self::DUMP_DIRECTORY . $imagePath;

        if (!$this->mediaDirectory->isExist($dumpImagePath)) {
            $this->mediaDirectory->copyFile($imagePath, $dumpImagePath);
        }
    }

    public function removeDumpImage($imagePath)
    {
        if ($this->configProvider->isDumpOriginal()) {
            $dumpImagePath = self::DUMP_DIRECTORY . $imagePath;

            if ($this->mediaDirectory->isExist($dumpImagePath)) {
                $this->mediaDirectory->delete($dumpImagePath);
            }
        }
    }

    /**
     * @param string $file
     *
     * @return string
     */
    private function dirname($file)
    {
        /** @codingStandardsIgnoreStart */
        $directory = dirname($file);
        /** @codingStandardsIgnoreEnd */

        return $directory;
    }
}
