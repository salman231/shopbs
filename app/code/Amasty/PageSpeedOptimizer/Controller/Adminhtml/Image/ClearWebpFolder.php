<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_PageSpeedOptimizer
 */


namespace Amasty\PageSpeedOptimizer\Controller\Adminhtml\Image;

use Amasty\PageSpeedOptimizer\Controller\Adminhtml\AbstractClearFolders;
use Amasty\PageSpeedOptimizer\Model\OptionSource\Resolutions;

/**
 * Class ClearWebpFolder clears WebP optimized images
 *
 * @package Amasty\PageSpeedOptimizer\Controller\Adminhtml\Image
 */
class ClearWebpFolder extends AbstractClearFolders
{
    public function execute()
    {
        $this->clearFolder(Resolutions::WEBP_DIR);
        $this->messageManager->addSuccessMessage(__('WebP Images Folder was successful cleaned.'));

        return $this->_redirect($this->_redirect->getRefererUrl());
    }
}
