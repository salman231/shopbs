<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_PageSpeedOptimizer
 */


namespace Amasty\PageSpeedOptimizer\Plugin;

/**
 * Class AdapterImage is plugin for real time image optimization saved via \Magento\Framework\Image
 *
 * @package Amasty\PageSpeedOptimizer
 */
class AdapterImage extends AbstractImage
{
    /**
     * @var string
     */
    private $image;

    /**
     * @param $subject
     * @param $path
     * @param $newFileName
     */
    public function beforeSave($subject, $path = null, $newFileName = null)
    {
        if ($path !== null) {
            if ($newFileName !== null) {
                $this->image = rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $newFileName;
            } else {
                $this->image = $path;
            }
        } else {
            $this->image = false;
        }
    }

    /**
     * @param $subject
     * @param $result
     *
     * @return mixed
     */
    public function afterSave($subject, $result)
    {
        if ($this->configProvider->isEnabled() && $this->configProvider->isOptimizeImages()
            && $this->configProvider->isAutomaticallyOptimizeImages() && $this->image
        ) {
            if ($image = $this->prepareFile($this->image)) {
                $this->imageProcessor->execute($image);
            }
        }

        return $result;
    }
}
