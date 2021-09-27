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

namespace Mageplaza\AutoRelated\Block\Adminhtml\Rule\Edit\Tab;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Helper\Data;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ProductFactory;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory;
use Magento\Framework\Data\Collection;
use Magento\Framework\DataObject;
use Magento\Framework\Module\Manager;
use Magento\Store\Model\WebsiteFactory;
use Mageplaza\AutoRelated\Model\RuleFactory;

/**
 * Class Grid
 * @package Mageplaza\AutoRelated\Block\Adminhtml\Rule\Edit\Tab
 */
class Grid extends \Magento\Catalog\Block\Adminhtml\Product\Grid
{
    /**
     * @var RuleFactory
     */
    protected $autoRelatedRuleFac;

    /**
     * Grid constructor.
     *
     * @param Context $context
     * @param Data $backendHelper
     * @param WebsiteFactory $websiteFactory
     * @param CollectionFactory $setsFactory
     * @param ProductFactory $productFactory
     * @param Type $type
     * @param Status $status
     * @param Visibility $visibility
     * @param Manager $moduleManager
     * @param RuleFactory $autoRelatedRuleFac
     * @param array $data
     */
    public function __construct(
        Context $context,
        Data $backendHelper,
        WebsiteFactory $websiteFactory,
        CollectionFactory $setsFactory,
        ProductFactory $productFactory,
        Type $type,
        Status $status,
        Visibility $visibility,
        Manager $moduleManager,
        RuleFactory $autoRelatedRuleFac,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $backendHelper,
            $websiteFactory,
            $setsFactory,
            $productFactory,
            $type,
            $status,
            $visibility,
            $moduleManager,
            $data
        );

        $this->autoRelatedRuleFac = $autoRelatedRuleFac;
    }

    /**
     * @param Collection $collection
     *
     * @return bool|void
     */
    public function setCollection($collection)
    {
        $ruleId = $this->getRequest()->getParam('id');
        $rule   = $this->autoRelatedRuleFac->create()->load($ruleId);
        if (!$rule) {
            return false;
        }
        $productIds = ($this->getRequest()->getParam('type') == 'cond')
            ? $rule->getMatchingProductIdsByCondition() : $rule->getMatchingProductIds();
        $collection->addIdFilter($productIds);

        parent::setCollection($collection);
    }

    /**
     * @inheritdoc
     */
    protected function _prepareColumns()
    {
        parent::_prepareColumns();

        $this->removeColumn('websites')
            ->removeColumn('edit')
            ->unsetChild('grid.bottom.links');

        $this->setFilterVisibility(false);

        return $this;
    }

    /**
     * @return $this
     */
    protected function _prepareMassaction()
    {
        return $this;
    }

    /**
     * @param Product|DataObject $row
     *
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl(
            'catalog/product/edit',
            ['store' => $this->getRequest()->getParam('store'), 'id' => $row->getId()]
        );
    }

    /**
     * Get Grid Url for product list
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/productlist', ['id' => $ruleId = $this->getRequest()->getParam('id')]);
    }
}
