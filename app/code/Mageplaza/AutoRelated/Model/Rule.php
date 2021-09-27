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

namespace Mageplaza\AutoRelated\Model;

use Exception;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\CatalogRule\Model\Data\Condition\Converter;
use Magento\CatalogRule\Model\Rule\Condition\Combine;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Model\ResourceModel\Iterator;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Rule\Model\AbstractModel;
use Magento\SalesRule\Model\Rule\Condition\CombineFactory;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\AutoRelated\Api\Data\AutoRelatedInterface;
use Mageplaza\AutoRelated\Model\Config\Source\Type;

/**
 * Class Rule
 * @package Mageplaza\AutoRelated\Model
 */
class Rule extends AbstractModel implements AutoRelatedInterface
{
    /**
     * Store matched product Ids
     *
     * @var array
     */
    protected $productIds;

    /**
     * Store matched product Ids in condition tab
     *
     * @var array
     */
    protected $productConditionsIds;

    /**
     * Store matched product Ids with rule id
     *
     * @var array
     */
    protected $dataProductIds;

    /**
     * @var Iterator
     */
    protected $resourceIterator;

    /**
     * @var ProductFactory
     */
    protected $productFactory;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var Visibility
     */
    protected $productVisibility;

    /**
     * @var Status
     */
    protected $productStatus;

    /**
     * @var \Magento\CatalogRule\Model\Rule\Condition\CombineFactory
     */
    protected $_productCombineFactory;

    /**
     * @var CombineFactory
     */
    protected $_salesCombineFactory;

    /**
     * @var \Mageplaza\AutoRelated\Helper\Rule
     */
    protected $helper;

    /**
     * @var Session
     */
    protected $checkoutSession;

    /**
     * @var Converter
     */
    protected $ruleConditionConverter;

    /**
     * @var ProductInterface[]
     */
    protected $matchProducts;

    /**
     * Rule constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param TimezoneInterface $localeDate
     * @param Status $productStatus
     * @param Visibility $productVisibility
     * @param ProductFactory $productFactory
     * @param \Magento\CatalogRule\Model\Rule\Condition\CombineFactory $catalogCombineFactory
     * @param CombineFactory $salesCombineFactory
     * @param Iterator $resourceIterator
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        TimezoneInterface $localeDate,
        Status $productStatus,
        Visibility $productVisibility,
        ProductFactory $productFactory,
        \Magento\CatalogRule\Model\Rule\Condition\CombineFactory $catalogCombineFactory,
        CombineFactory $salesCombineFactory,
        Iterator $resourceIterator,
        \Mageplaza\AutoRelated\Helper\Rule $helper,
        Session $checkoutSession,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->_productCombineFactory = $catalogCombineFactory;
        $this->_salesCombineFactory   = $salesCombineFactory;
        $this->resourceIterator       = $resourceIterator;
        $this->productFactory         = $productFactory;
        $this->productVisibility      = $productVisibility;
        $this->productStatus          = $productStatus;
        $this->helper                 = $helper;
        $this->checkoutSession        = $checkoutSession;

        parent::__construct($context, $registry, $formFactory, $localeDate, $resource, $resourceCollection, $data);
    }

    /**
     * @return void
     */
    public function _construct()
    {
        parent::_construct();
        $this->_init('Mageplaza\AutoRelated\Model\ResourceModel\Rule');
        $this->setIdFieldName('rule_id');
    }

    /**
     * Get rule condition combine model instance
     *
     * @return Combine|\Magento\SalesRule\Model\Rule\Condition\Combine
     */
    public function getConditionsInstance()
    {
        $type = $this->_registry->registry('autorelated_type');
        if ($type === Type::TYPE_PAGE_SHOPPING || $type === Type::TYPE_PAGE_OSC) {
            return $this->_salesCombineFactory->create();
        }

        return $this->_productCombineFactory->create();
    }

    /**
     * Get rule condition product combine model instance
     *
     * @return Combine
     */
    public function getActionsInstance()
    {
        return $this->_productCombineFactory->create();
    }

    /**
     * @param string $formName
     *
     * @return string
     */
    public function getConditionsFieldSetId($formName = '')
    {
        return $formName . 'rule_conditions_fieldset_' . $this->getId();
    }

    /**
     * @param string $formName
     *
     * @return string
     */
    public function getActionsFieldSetId($formName = '')
    {
        return $formName . 'rule_actions_fieldset_' . $this->getId();
    }

    /**
     * @return bool
     */
    public function hasChild()
    {
        $ruleChild = $this->getChild();
        if (!empty($ruleChild)) {
            return true;
        }

        return false;
    }

    /**
     * @return bool
     */
    public function hasChildActive()
    {
        $ruleChild = $this->getChild();

        return !empty($ruleChild) && $ruleChild['is_active'];
    }

    /**
     * @return mixed
     */
    public function getChild()
    {
        return $this->getResource()->getRuleData($this->getId(), 'parent_id');
    }

    /**
     * {@inheritdoc}
     */
    public function afterSave()
    {
        if ($this->getCustomerGroupIds() || $this->getStoreIds()) {
            $this->getResource()->deleteOldData($this->getId());
            if ($storeIds = $this->getStoreIds()) {
                $this->getResource()->updateStore($storeIds, $this->getId());
            }
            if ($groupIds = $this->getCustomerGroupIds()) {
                $this->getResource()->updateCustomerGroup($groupIds, $this->getId());
            }
        }

        $this->reindex();

        return parent::afterSave();
    }

    /**
     * @return $this
     */
    public function reindex()
    {
        $this->getMatchingProductIds();
        $this->getResource()->deleteActionIndex($this->getId());
        if (!empty($this->dataProductIds) && is_array($this->dataProductIds)) {
            $this->getResource()->insertActionIndex($this->dataProductIds);
        }

        return $this;
    }

    /**
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getApplyProductIds()
    {
        $productIds = [];
        switch ($this->getData('block_type')) {
            case Type::TYPE_PAGE_PRODUCT:
                $product = $this->helper->getCurrentProduct();
                if ($this->getConditions()->validate($product)) {
                    $productIds = $this->getResource()->getProductListByRuleId($this->getId(), $product->getId());
                }
                break;
            case Type::TYPE_PAGE_CATEGORY:
                if ($condition = $this->getCategoryConditionsSerialized()) {
                    try {
                        $categoryIds = $this->helper->unserialize($condition);
                        $category    = $this->helper->getCurrentCategory();
                        if (in_array($category->getId(), $categoryIds)) {
                            $productIds = $this->getResource()->getProductListByRuleId($this->getId());
                        }
                    } catch (Exception $e) {
                        $this->_logger->critical($e->getMessage());
                    }
                }
                break;
            case Type::TYPE_PAGE_SHOPPING:
                $quote   = $this->checkoutSession->getQuote();
                $address = $quote->isVirtual() ? $quote->getBillingAddress() : $quote->getShippingAddress();
                if ($this->getConditions()->validate($address)) {
                    $productIds = $this->getResource()->getProductListByRuleId($this->getId());
                }
                break;
            case Type::TYPE_PAGE_OSC:
                $quote   = $this->checkoutSession->getQuote();
                $address = $quote->isVirtual() ? $quote->getBillingAddress() : $quote->getShippingAddress();
                if ($this->getConditions()->validate($address)) {
                    $productIds = $this->getResource()->getProductListByRuleId($this->getId());
                }
                break;
        }

        return $productIds;
    }

    /**
     * {@inheritdoc}
     */
    public function getMatchingProductIds()
    {
        if ($this->productIds === null) {
            $this->productIds = [];
            $this->setCollectedAttributes([]);

            $productCollection = $this->getProductCollection();
            $this->getActions()->collectValidatedAttributes($productCollection);

            $this->resourceIterator->walk(
                $productCollection->getSelect(),
                [[$this, 'callbackValidateProduct']],
                [
                    'attributes' => $this->getCollectedAttributes(),
                    'product'    => $this->productFactory->create()
                ]
            );
        }

        return $this->productIds;
    }

    /**
     * {@inheritdoc}
     */
    public function getMatchingProducts()
    {
        if (!$this->matchProducts) {
            $productIds = $this->getResource()->getProductListByRuleId($this->getId());
            if (empty($productIds)) {
                return [];
            }
            $productCollection = $this->getProductCollection();
            $productCollection->addIdFilter($productIds);
            $this->matchProducts = $productCollection->getItems();
        }

        return $this->matchProducts;
    }

    /**
     * {@inheritdoc}
     */
    public function setMatchingProducts($products)
    {
        $this->matchProducts = $products;

        return $this;
    }

    /**
     * @return array|null
     */
    public function getMatchingProductIdsByCondition()
    {
        if ($this->productConditionsIds === null) {
            $this->productConditionsIds = [];
            $this->setCollectedAttributes([]);

            $productCollection = $this->getProductCollection();
            $this->getConditions()->collectValidatedAttributes($productCollection);

            $this->resourceIterator->walk(
                $productCollection->getSelect(),
                [[$this, 'callbackValidateProductConditions']],
                [
                    'attributes' => $this->getCollectedAttributes(),
                    'product'    => $this->productFactory->create()
                ]
            );
        }

        return $this->productConditionsIds;
    }

    /**
     * @return Collection
     */
    public function getProductCollection()
    {
        /** @var $productCollection Collection */
        $productCollection = $this->productFactory->create()->getCollection();
        $productCollection->addAttributeToSelect('*')
            ->setVisibility([
                Visibility::VISIBILITY_IN_CATALOG,
                Visibility::VISIBILITY_BOTH
            ])
            ->addAttributeToFilter('status', 1);

        return $productCollection;
    }

    /**
     * Callback function for product matching
     *
     * @param array $args
     *
     * @return void
     */
    public function callbackValidateProduct($args)
    {
        $product = clone $args['product'];
        $product->setData($args['row']);
        $ruleId = $this->getRuleId();
        if ($ruleId && $this->getActions()->validate($product)) {
            $this->productIds[]     = $product->getId();
            $this->dataProductIds[] = ['rule_id' => $ruleId, 'product_id' => $product->getId()];
        }
    }

    /**
     * Callback function for product matching (conditions)
     *
     * @param array $args
     *
     * @return void
     */
    public function callbackValidateProductConditions($args)
    {
        $product = clone $args['product'];
        $product->setData($args['row']);
        $ruleId = $this->getRuleId();
        if ($ruleId && $this->getConditions()->validate($product)) {
            $this->productConditionsIds[] = $product->getId();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getRuleId()
    {
        return $this->getData(self::RULE_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setRuleId($value)
    {
        return $this->setData(self::RULE_ID, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->getData(self::NAME);
    }

    /**
     * {@inheritdoc}
     */
    public function setName($value)
    {
        return $this->setData(self::NAME, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockType()
    {
        return $this->getData(self::BLOCK_TYPE);
    }

    /**
     * {@inheritdoc}
     */
    public function setBlockType($value)
    {
        return $this->setData(self::BLOCK_TYPE, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getFromDate()
    {
        return $this->getData(self::FROM_DATE);
    }

    /**
     * {@inheritdoc}
     */
    public function setFromDate($value)
    {
        return $this->setData(self::FROM_DATE, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getToDate()
    {
        return $this->getData(self::TO_DATE);
    }

    /**
     * {@inheritdoc}
     */
    public function setToDate($value)
    {
        return $this->setData(self::TO_DATE, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getIsActive()
    {
        return $this->getData(self::IS_ACTIVE);
    }

    /**
     * {@inheritdoc}
     */
    public function setIsActive($value)
    {
        return $this->setData(self::IS_ACTIVE, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getRuleConditions()
    {
        return $this->getRuleConditionConverter()->arrayToDataModel($this->getConditions()->asArray());
    }

    /**
     * {@inheritdoc}
     */
    public function setRuleConditions($condition)
    {
        $this->getConditions()
            ->setConditions([])
            ->loadArray($this->getRuleConditionConverter()->dataModelToArray($condition));

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getRuleActions()
    {
        return $this->getRuleConditionConverter()->arrayToDataModel($this->getActions()->asArray());
    }

    /**
     * {@inheritdoc}
     */
    public function setRuleActions($condition)
    {
        $this->getActions()
            ->setActions([])
            ->loadArray($this->getRuleConditionConverter()->dataModelToArray($condition));

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getCategoryConditions()
    {
        return $this->getData(self::CATEGORY_CONDITIONS_SERIALIZED);
    }

    /**
     * {@inheritdoc}
     */
    public function setCategoryConditions($value)
    {
        return $this->setData(self::CATEGORY_CONDITIONS_SERIALIZED, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getSortOrder()
    {
        return $this->getData(self::SORT_ORDER);
    }

    /**
     * {@inheritdoc}
     */
    public function setSortOrder($value)
    {
        return $this->setData(self::SORT_ORDER, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getParentId()
    {
        return $this->getData(self::PARENT_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setParentId($value)
    {
        return $this->setData(self::PARENT_ID, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getImpression()
    {
        return $this->getData(self::IMPRESSION);
    }

    /**
     * {@inheritdoc}
     */
    public function setImpression($value)
    {
        return $this->setData(self::IMPRESSION, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getClick()
    {
        return $this->getData(self::CLICK);
    }

    /**
     * {@inheritdoc}
     */
    public function setClick($value)
    {
        return $this->setData(self::CLICK, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getLocation()
    {
        return $this->getData(self::LOCATION);
    }

    /**
     * {@inheritdoc}
     */
    public function setLocation($value)
    {
        return $this->setData(self::LOCATION, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockName()
    {
        return $this->getData(self::BLOCK_NAME);
    }

    /**
     * {@inheritdoc}
     */
    public function setBlockName($value)
    {
        return $this->setData(self::BLOCK_NAME, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getLimitNumber()
    {
        return $this->getData(self::LIMIT_NUMBER);
    }

    /**
     * {@inheritdoc}
     */
    public function setLimitNumber($value)
    {
        return $this->setData(self::LIMIT_NUMBER, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getDisplayOutOfStock()
    {
        return $this->getData(self::DISPLAY_OUT_OF_STOCK);
    }

    /**
     * {@inheritdoc}
     */
    public function setDisplayOutOfStock($value)
    {
        return $this->setData(self::DISPLAY_OUT_OF_STOCK, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getProductLayout()
    {
        return $this->getData(self::PRODUCT_LAYOUT);
    }

    /**
     * {@inheritdoc}
     */
    public function setProductLayout($value)
    {
        return $this->setData(self::PRODUCT_LAYOUT, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getSortOrderDirection()
    {
        return $this->getData(self::SORT_ORDER_DIRECTION);
    }

    /**
     * {@inheritdoc}
     */
    public function setSortOrderDirection($value)
    {
        return $this->setData(self::SORT_ORDER_DIRECTION, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getDisplayAdditional()
    {
        return $this->getData(self::DISPLAY_ADDITIONAL);
    }

    /**
     * {@inheritdoc}
     */
    public function setDisplayAdditional($value)
    {
        return $this->setData(self::DISPLAY_ADDITIONAL, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getAddRucProduct()
    {
        return $this->getData(self::ADD_RUC_PRODUCT);
    }

    /**
     * {@inheritdoc}
     */
    public function setAddRucProduct($value)
    {
        return $this->setData(self::ADD_RUC_PRODUCT, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getProductNotDisplayed()
    {
        return $this->getData(self::PRODUCT_NOT_DISPLAYED);
    }

    /**
     * {@inheritdoc}
     */
    public function setProductNotDisplayed($value)
    {
        return $this->setData(self::PRODUCT_NOT_DISPLAYED, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getTotalImpression()
    {
        return $this->getData(self::TOTAL_IMPRESSION);
    }

    /**
     * {@inheritdoc}
     */
    public function setTotalImpression($value)
    {
        return $this->setData(self::TOTAL_IMPRESSION, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getTotalClick()
    {
        return $this->getData(self::TOTAL_CLICK);
    }

    /**
     * {@inheritdoc}
     */
    public function setTotalClick($value)
    {
        return $this->setData(self::TOTAL_CLICK, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getCreatedAt()
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * {@inheritdoc}
     */
    public function setCreatedAt($value)
    {
        return $this->setData(self::CREATED_AT, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getUpdatedAt()
    {
        return $this->getData(self::UPDATED_AT);
    }

    /**
     * {@inheritdoc}
     */
    public function setUpdatedAt($value)
    {
        return $this->setData(self::UPDATED_AT, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getDisplayMode()
    {
        return $this->getData(self::DISPLAY_MODE);
    }

    /**
     * {@inheritdoc}
     */
    public function setDisplayMode($value)
    {
        return $this->setData(self::DISPLAY_MODE, $value);
    }

    /**
     * Getter for the rule condition converter
     *
     * @return Converter
     */
    private function getRuleConditionConverter()
    {
        if ($this->ruleConditionConverter === null) {
            $this->ruleConditionConverter = ObjectManager::getInstance()
                ->get(Converter::class);
        }

        return $this->ruleConditionConverter;
    }
}
