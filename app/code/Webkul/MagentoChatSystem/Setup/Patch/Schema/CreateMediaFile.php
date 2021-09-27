<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MagentoChatSystem
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MagentoChatSystem\Setup\Patch\Schema;

use Magento\Framework\Setup\Patch\SchemaPatchInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Patch is mechanism, that allows to do atomic upgrade data changes
 */
class CreateMediaFile implements SchemaPatchInterface
{
    /**
     * @var ModuleDataSetupInterface $moduleDataSetup
     */
    private $moduleDataSetup;

    /**
     * @param \Magento\Framework\Module\Dir\Reader $reader
     * @param \Magento\Framework\Filesystem\Io\File $io
     * @param \Magento\Framework\Filesystem $fileIo
     * @param DirectoryList $directoryList
     */
    public function __construct(
        \Magento\Framework\Module\Dir\Reader $reader,
        \Magento\Framework\Filesystem\Io\File $io,
        \Magento\Framework\Filesystem $fileIo,
        DirectoryList $directoryList
    ) {
        $this->reader = $reader;
        $this->fileIo = $io;
        $this->filesystem = $fileIo;
        $this->directoryList = $directoryList;
    }

    /**
     * Do Upgrade
     *
     * @return void
     */
    public function apply()
    {
        $rootFullPath = $this->directoryList->getRoot();
        if (!$this->fileIo->fileExists($rootFullPath.'/app.js')) {
            $serverJs = $this->reader->getModuleDir(
                '',
                'Webkul_MagentoChatSystem'
            ).'/etc/serverJs/app.js';
            $this->fileIo->cp($serverJs, $rootFullPath.'/app.js');
        }

        if (!$this->fileIo->fileExists($rootFullPath.'/package.json')) {
            $serverJs = $this->reader->getModuleDir(
                '',
                'Webkul_MagentoChatSystem'
            ).'/etc/serverJs/package.json';
            $this->fileIo->cp($serverJs, $rootFullPath.'/package.json');
        }

        $mediaFullPath = $this->filesystem->getDirectoryRead(
            \Magento\Framework\App\Filesystem\DirectoryList::MEDIA
        )->getAbsolutePath('chatsystem');
        if (!$this->fileIo->fileExists($mediaFullPath)) {
            $this->fileIo->mkdir($mediaFullPath, 0777, true);
            $defaultImage = $this->reader->getModuleDir(
                '',
                'Webkul_MagentoChatSystem'
            ).'/view/frontend/web/images/default.png';
            $this->fileIo->cp($defaultImage, $mediaFullPath.'/default.png');
        }

        $mediaFullPath = $this->filesystem->getDirectoryRead(
            \Magento\Framework\App\Filesystem\DirectoryList::MEDIA
        )->getAbsolutePath('chatsystem/admin');

        if (!$this->fileIo->fileExists($mediaFullPath)) {
            $this->fileIo->mkdir($mediaFullPath, 0777, true);
            $defaultImage = $this->reader->getModuleDir(
                '',
                'Webkul_MagentoChatSystem'
            ).'/view/adminhtml/web/images/default.png';
            $this->fileIo->cp($defaultImage, $mediaFullPath.'/default.png');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [];
    }
}
