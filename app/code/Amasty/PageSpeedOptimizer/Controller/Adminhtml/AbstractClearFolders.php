<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_PageSpeedOptimizer
 */


namespace Amasty\PageSpeedOptimizer\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Class AbstractClearFolders common class for clearing optimized images folders
 *
 * @package Amasty\PageSpeedOptimizer
 */
abstract class AbstractClearFolders extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Amasty_PageSpeedOptimizer::config';

    /**
     * @var Filesystem\Directory\WriteInterface
     */
    private $mediaDirectory;

    public function __construct(
        Filesystem $filesystem,
        Action\Context $context
    ) {
        $this->mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        parent::__construct($context);
    }

    public function clearFolder($folder)
    {
        if ($this->mediaDirectory->isDirectory($folder)) {
            foreach ($this->mediaDirectory->read($folder) as $item) {
                try {
                    /** @codingStandardsIgnoreStart */
                    $this->mediaDirectory->delete($item);
                    /** @codingStandardsIgnoreEnd */
                } catch (\Exception $e) {
                    null;
                }
            }
        }
    }
}
