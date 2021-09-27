<?php
/**
 * Webkul Software.
 *
 *
 *
 * @category  Webkul
 * @package   Webkul_MobikulApi
 * @author    Webkul <support@webkul.com>
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html ASL Licence
 * @link      https://store.webkul.com/license.html
 */

namespace Webkul\MobikulApi\Controller\Extra;

class GetImage extends \Magento\Framework\App\Action\Action
{
    protected $baseDir;
    protected $bannerImage;
    protected $helperCatalog;
    protected $carouselImageFactory;

    /**
     * Constructer method.
     *
     * @param \Magento\Framework\App\Action\Context $context context object
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Filesystem\DirectoryList $dir,
        \Webkul\MobikulCore\Helper\Catalog $helperCatalog,
        \Webkul\MobikulCore\Model\BannerimageFactory $bannerImage,
        \Webkul\MobikulCore\Model\CarouselimageFactory $carouselImageFactory
    ) {
        $this->bannerImage = $bannerImage;
        $this->helperCatalog = $helperCatalog;
        $this->baseDir = $dir->getPath("media");
        $this->carouselImageFactory = $carouselImageFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $wholeData = $this->getRequest()->getParams();
        $id = $wholeData["id"] ?? 0;
        $type = $wholeData["type"] ?? "";
        $width = $wholeData["width"] ?? 750;
        $mFactor = $wholeData["mFactor"] ?? 1;
        $mFactor = ceil($mFactor) > 2.5 ? 2.5 : ceil($mFactor);
        $id = (int)$id;
        if ($id > 0) {
            if ($type == "carouselbanner") {
                $bannerWidth = $width*$mFactor;
                $bannerWidth = $bannerWidth > 2500 ? 2500 : $bannerWidth;
                $bannerHeight = ($mFactor*($width/3));
                $bannerHeight = $bannerHeight > 2500 ? 2500 : $bannerHeight;
                $banner = $this->carouselImageFactory->create()->load($id);
                $basePath = $this->baseDir."/".$banner->getFilename();
                if ($this->fileDriver->isFile($basePath)) {
                    $newPath = $this->baseDir."/"."mobikulresized"."/".$bannerWidth."x".
                        $bannerHeight."/".$banner->getFilename();
                    $this->helperCatalog->resizeNCache($basePath, $newPath, $bannerWidth, $bannerHeight);
                    $this->getResponse()->setHeader("Content-Type", "image/png", true);
                    $this->fileDriver->fileRead($newPath);
                }
            } elseif ($type == "banner") {
                $bannerWidth = $width*$mFactor;
                $bannerWidth = $bannerWidth > 2500 ? 2500 : $bannerWidth;
                $bannerHeight = ($mFactor*(2*($width/3)));
                $bannerHeight = $bannerHeight > 2500 ? 2500 : $bannerHeight;
                $banner = $this->bannerImage->create()->load($id);
                $basePath = $this->baseDir."/".$banner->getFilename();
                if ($this->fileDriver->isFile($basePath)) {
                    $newPath = $this->baseDir."/"."mobikulresized"."/".$bannerWidth."x".
                        $bannerHeight."/".$banner->getFilename();
                    $this->helperCatalog->resizeNCache($basePath, $newPath, $bannerWidth, $bannerHeight);
                    $this->getResponse()->setHeader("Content-Type", "image/png", true);
                    $this->fileDriver->fileRead($newPath);
                }
            }
        }
    }
}
