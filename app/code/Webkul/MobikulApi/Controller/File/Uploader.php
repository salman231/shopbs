<?php
/**
 * Webkul Software.
 *
 * PHP version 7.0+
 *
 * @category  Webkul
 * @package   Webkul_MobikulApi
 * @author    Webkul <support@webkul.com>
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html ASL Licence
 * @link      https://store.webkul.com/license.html
 */

namespace Webkul\MobikulApi\Controller\File;

use Magento\Framework\App\Response\Http;

class Uploader extends \Magento\Framework\File\Uploader
{

    /**
     * Validate callbacks storage
     *
     * @var array
     * @access protected
     */
    protected $_validateCallbacks = [];

    public function __construct(
        $fileId = "",
        \Magento\Framework\File\Mime $fileMime = null,
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList = null
    ) {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $urlInterface = $objectManager->create(\Magento\Framework\UrlInterface::class);
        $resultRedirectFactory = $objectManager->create(\Magento\Framework\Controller\Result\RedirectFactory::class);
        $url = $urlInterface->getCurrentUrl();
        
            parent::__construct(
                $fileId,
                $fileMime,
                $directoryList
            );
    }
}
