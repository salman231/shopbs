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
 * @package     Mageplaza_AutoRelated
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\AutoRelated\Helper;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\AutoRelated\Model\ResourceModel\Rule\Collection;
use Mageplaza\AutoRelated\Model\ResourceModel\Rule\CollectionFactory;

/**
 * Class Data
 * @package Mageplaza\FrequentlyBought\Helper
 */
class Rule extends Data
{
    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var DateTime
     */
    protected $dateTime;

    /**
     * Rule constructor.
     *
     * @param Context $context
     * @param ObjectManagerInterface $objectManager
     * @param StoreManagerInterface $storeManager
     * @param Registry $registry
     * @param Session $customerSession
     * @param DateTime $dateTime
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        Context $context,
        ObjectManagerInterface $objectManager,
        StoreManagerInterface $storeManager,
        Registry $registry,
        Session $customerSession,
        DateTime $dateTime,
        CollectionFactory $collectionFactory
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->dateTime          = $dateTime;

        parent::__construct($context, $objectManager, $storeManager, $registry, $customerSession);
    }

    /**
     * @return bool
     */
    public function isEnableArpBlock()
    {
        if (!$this->getData('arp_enable')) {
            $enable = false;
            if ($this->isEnabled()) {
                switch ($this->_request->getFullActionName()) {
                    case 'catalog_product_view':
                        $this->setData('type', 'product');
                        $product = $this->registry->registry('current_product');
                        $this->setData('entity_id', $product ? $product->getId() : '');
                        $enable = $product ? !$product->getMpDisableAutoRelated() : false;
                        break;
                    case 'catalog_category_view':
                        $this->setData('type', 'category');
                        $category = $this->registry->registry('current_category');
                        $this->setData('entity_id', $category ? $category->getId() : '');
                        $enable = true;
                        break;
                    case 'checkout_cart_index':
                        $this->setData('type', 'cart');
                        $enable = true;
                        break;
                    case 'onestepcheckout_index_index':
                        $this->setData('type', 'osc');
                        $enable = true;
                        break;
                }
            }

            $this->setData('arp_enable', $enable);
        }

        return $this->getData('arp_enable');
    }

    /**
     * @param $mode
     *
     * @return array|null
     */
    public function getActiveRulesByMode($mode)
    {
        if (!$this->getData('rule_mode_' . $mode)) {
            $rules = [];
            foreach ($this->getActiveRules() as $rule) {
                if ($rule->getDisplayMode() === $mode) {
                    $rules[] = $rule;
                }
            }

            $this->setData('rule_mode_' . $mode, $rules);
        }

        return $this->getData('rule_mode_' . $mode);
    }

    /**
     * @return Collection
     */
    public function getActiveRules()
    {
        if (!$this->getData('active_rules')) {
            /** @var Collection $ruleCollections */
            $ruleCollections = $this->collectionFactory->create();
            $ruleCollections->addActiveFilter($this->getCustomerGroup(), $this->getCurrentStore())
                ->addDateFilter($this->dateTime->date())
                ->addTypeFilter($this->getData('type'))
                ->addLocationFilter(['neq' => 'custom']);

            $this->setData('active_rules', $ruleCollections);
        }

        return $this->getData('active_rules');
    }

    /**
     * Retrieve custom rules
     *
     * @return Collection
     */
    public function getCustomRules()
    {
        if (!$this->getData('custom_rules')) {
            /** @var Collection $ruleCollections */
            $ruleCollections = $this->collectionFactory->create();
            $ruleCollections->addActiveFilter($this->getCustomerGroup(), $this->getCurrentStore())
                ->addDateFilter($this->dateTime->date())
                ->addTypeFilter($this->getData('type'))
                ->addLocationFilter(['eq' => 'custom']);

            $this->setData('custom_rules', $ruleCollections);
        }

        return $this->getData('custom_rules');
    }
}
