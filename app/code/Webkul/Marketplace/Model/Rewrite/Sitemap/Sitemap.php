<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Marketplace
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Marketplace\Model\Rewrite\Sitemap;

use Magento\Framework\App\ObjectManager;
use Webkul\Marketplace\Helper\Data as MpHelper;

class Sitemap extends \Magento\Sitemap\Model\Sitemap
{
    /**
     * Initialize sitemap
     *
     * @return void
     */
    protected function _initSitemapItems()
    {
        $helper = ObjectManager::getInstance()->create(MpHelper::class);
        if (!$helper->includeSellerUrlInSitemap()) {
            return parent::_initSitemapItems();
        }

        parent::_initSitemapItems();
        $error = "";
        try {
            $this->sitemapItemFactory = ObjectManager::getInstance()->get(
                \Magento\Sitemap\Model\SitemapItemInterfaceFactory::class
            );
            $baseUrl = $this->_storeManager->getStore()->getBaseUrl();
            $includeProfileUrl = $helper->includeProfileUrlInSitemap();
            $profileFrequency = $helper->getFrequencyOfProfileUrlInSitemap();
            $profilePriority = $helper->getPriorityOfProfileUrlInSitemap();
            $includeCollectionUrl = $helper->includeCollectionUrlInSitemap();
            $collectionFrequency = $helper->getFrequencyOfCollectionUrlInSitemap();
            $collectionPriority = $helper->getPriorityOfCollectionUrlInSitemap();
            $fields = ["shop_url", "shop_title", "updated_at"];
            $sellerCollection = $helper->getSellerCollection();
            $sellerCollection->resetColumns();
            $sellerCollection->addFieldsToCollection($fields);
            $sellerCollection->addStoreWiseSellerColumns();
            $sellerCollection->addAllSellerUrls();

            foreach ($sellerCollection as $seller) {
                $updatedAt = $seller->getUpdatedAt();
                if ($includeProfileUrl) {
                    $this->_sitemapItems[] = $this->sitemapItemFactory->create([
                        'url' => $seller->getProfileUrl(),
                        'updatedAt' => $updatedAt,
                        'images' => [],
                        'priority' => $profilePriority,
                        'changeFrequency' => $profileFrequency,
                    ]);
                }

                if ($includeCollectionUrl) {
                    $this->_sitemapItems[] = $this->sitemapItemFactory->create([
                        'url' => $seller->getCollectionUrl(),
                        'updatedAt' => $updatedAt,
                        'images' => [],
                        'priority' => $collectionPriority,
                        'changeFrequency' => $collectionFrequency,
                    ]);
                }
            }
        } catch (\Exception $e) {
            $error = $e->getMessage();
        }
    }
}
