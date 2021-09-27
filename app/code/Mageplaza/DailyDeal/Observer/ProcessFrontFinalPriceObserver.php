<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_DailyDeal
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\DailyDeal\Observer;

use DateTime;
use Magento\CatalogRule\Observer\RulePricesStorage;
use Magento\Customer\Model\Session as CustomerModelSession;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\DailyDeal\Model\ResourceModel\DealFactory;

/**
 * Class ProcessFrontFinalPriceObserver
 * @package Mageplaza\DailyDeal\Observer
 */
class ProcessFrontFinalPriceObserver implements ObserverInterface
{
    /**
     * @var CustomerModelSession
     */
    protected $customerSession;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var TimezoneInterface
     */
    protected $localeDate;

    /**
     * @var DealFactory
     */
    protected $resourceDealFactory;

    /**
     * @var RulePricesStorage
     */
    protected $dealPricesStorage;

    /**
     * ProcessFrontFinalPriceObserver constructor.
     *
     * @param DealPricesStorage $dealPricesStorage
     * @param DealFactory $resourceRuleFactory
     * @param StoreManagerInterface $storeManager
     * @param TimezoneInterface $localeDate
     * @param CustomerModelSession $customerSession
     */
    public function __construct(
        DealPricesStorage $dealPricesStorage,
        DealFactory $resourceRuleFactory,
        StoreManagerInterface $storeManager,
        TimezoneInterface $localeDate,
        CustomerModelSession $customerSession
    ) {
        $this->dealPricesStorage   = $dealPricesStorage;
        $this->resourceDealFactory = $resourceRuleFactory;
        $this->storeManager        = $storeManager;
        $this->localeDate          = $localeDate;
        $this->customerSession     = $customerSession;
    }

    /**
     * Apply catalog price rules to product on frontend
     *
     * @param Observer $observer
     *
     * @return $this|void
     * @throws NoSuchEntityException
     */
    public function execute(Observer $observer)
    {
        $product   = $observer->getEvent()->getProduct();
        $productId = $product->getId();
        $storeId   = $product->getStoreId();

        if ($observer->hasDate()) {
            $date = new DateTime($observer->getEvent()->getDate());
        } else {
            $date = (new DateTime())->setTimestamp($this->localeDate->scopeTimeStamp($storeId));
        }

        if ($observer->hasWebsiteId()) {
            $wId = $observer->getEvent()->getWebsiteId();
        } else {
            $wId = $this->storeManager->getStore($storeId)->getWebsiteId();
        }

        if ($observer->hasCustomerGroupId()) {
            $gId = $observer->getEvent()->getCustomerGroupId();
        } elseif ($product->hasCustomerGroupId()) {
            $gId = $product->getCustomerGroupId();
        } else {
            $gId = $this->customerSession->getCustomerGroupId();
        }

        $key = "{$date->format('Y-m-d H:i:s')}|{$wId}|{$gId}|{$productId}";

        if (!$this->dealPricesStorage->hasDealPrice($key)) {
            $dealPrices = $this->resourceDealFactory->create()->getDealPrices($date, $storeId, $productId);
            $this->dealPricesStorage->setDealPrice(
                $key,
                isset($dealPrices[$productId]) ? $dealPrices[$productId] : false
            );
        }
        if ($this->dealPricesStorage->getDealPrice($key) !== false) {
            $finalPrice = min($product->getData('final_price'), $this->dealPricesStorage->getDealPrice($key));
            $product->setFinalPrice($finalPrice);
        }

        return $this;
    }
}
