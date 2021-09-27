<?php
/**
 * Webkul Software.
 *
 * PHP version 7.0+
 *
 * @category  Webkul
 * @package   Webkul_DeliveryBoy
 * @author    Webkul <support@webkul.com>
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html ASL Licence
 * @link      https://store.webkul.com/license.html
 */
namespace Webkul\DeliveryBoy\Helper;

use Psr\Log\LoggerInterface;

class Catalog extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Catalog\Helper\Image
     */
    private $imageHelper;
    
    /**
     * @var \Magento\Store\Block\Switcher
     */
    private $storeSwitcher;

    /**
     * @var \Magento\Framework\App\Helper\Context
     */
    private $context;

    /**
     * @var \Magento\Framework\Image\Factory
     */
    private $imageFactory;

    /**
     * @var \Webkul\DeliveryBoy\Helper\Data
     */
    private $helperData;

    /**
     * @var \Magento\Framework\Filesystem\Driver\File
     */
    private $fileDriver;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param \Magento\Catalog\Helper\Image $imageHelper
     * @param \Magento\Store\Block\Switcher $storeSwitcher
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\Image\Factory $imageFactory
     * @param \Webkul\DeliveryBoy\Helper\Data $helperData
     * @param \Magento\Framework\Filesystem\Driver\File $fileDriver
     * @param LoggerInterface $logger
     */
    public function __construct(
        \Magento\Catalog\Helper\Image $imageHelper,
        \Magento\Store\Block\Switcher $storeSwitcher,
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Image\Factory $imageFactory,
        \Webkul\DeliveryBoy\Helper\Data $helperData,
        \Magento\Framework\Filesystem\Driver\File $fileDriver,
        LoggerInterface $logger
    ) {
        parent::__construct($context);
        
        $this->imageHelper = $imageHelper;
        $this->imageFactory = $imageFactory;
        $this->storeSwitcher = $storeSwitcher;
        $this->helperData = $helperData;
        $this->fileDriver = $fileDriver;
        $this->logger = $logger;
    }

    /**
     * @param int $websiteId
     * @return array
     */
    public function getStoreData(string $websiteId = "1"): array
    {
        $storeData = [];
        try {
            foreach ($this->storeSwitcher->getGroups() as $group) {
                if ($group->getWebsiteId() == $websiteId) {
                    $groupArr = [];
                    $groupArr["id"] = $group->getGroupId();
                    $groupArr["name"] = $group->getName();
                    $stores = $group->getStores();
                    foreach ($stores as $store) {
                        if (!$store->isActive()) {
                            continue;
                        }
                        $storeArr = [];
                        $storeArr["id"] = $store->getStoreId();
                        $code = explode("_", $this->helperData->getLocaleCodes($store->getId()));
                        $storeArr["code"] = $code[0];
                        $storeArr["name"] = $store->getName();
                        $groupArr["stores"][] = $storeArr;
                    }
                    $storeData[] = $groupArr;
                } else {
                    continue;
                }
            }
            return $storeData;
        } catch (\Throwable $t) {
            $this->logger->critical($t->getMessage());
        }
    }

    /**
     * @param string $data data
     * @return string
     */
    public function stripTags($data)
    {
        return strip_tags($data);
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @param int $size
     * @param string $imageType
     * @param bool $keepFrame
     * @return string
     */
    public function getImageUrl(
        \Magento\Catalog\Model\Product $product,
        int $size,
        string $imageType = "product_page_image_small",
        bool $keepFrame = true
    ) {
        try {
            return $this->imageHelper
                ->init($product, $imageType)
                ->keepFrame($keepFrame)
                ->resize($size)
                ->getUrl();
        } catch (\Throwable $t) {
            $this->logger->critical($t->getMessage());
        }
    }

    /**
     * @param string $basePath
     * @param string $newPath
     * @param integer $width
     * @param integer $height
     * @param bool $forCustomer
     *
     * @return string
     */
    public function resizeNCache(
        string $basePath,
        string $newPath,
        int $imageWidth,
        int $imageHeight,
        bool $forCustomer = false
    ) {
        try {
            if (!$this->fileDriver->isFile($newPath) || $forCustomer) {
                $imageObj = $this->imageFactory->create($basePath);
                $imageObj->keepAspectRatio(false);
                $imageObj->backgroundColor([255, 255, 255]);
                $imageObj->keepFrame(false);
                $imageObj->resize($imageWidth, $imageHeight);
                $imageObj->save($newPath);
            }
        } catch (\Throwable $t) {
            $this->logger->critical($t->getMessage());
        }
    }
}
