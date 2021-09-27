<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MobikulCore
 * @author    Webkul <support@webkul.com>
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html ASL Licence
 * @link      https://store.webkul.com/license.html
 */

namespace Webkul\MobikulCore\Block\Adminhtml\Notification\Edit\Tab;

use Magento\Store\Model\Store;

/**
 * Class Product Grid block
 */
class ProductGrid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    protected $type;
    protected $status;
    protected $visibility;
    protected $setsFactory;
    protected $moduleManager;
    protected $productFactory;
    protected $websiteFactory;
    protected $customCollectionNotification;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Store\Model\WebsiteFactory $websiteFactory
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory $setsFactory
     * @param \Webkul\MobikulCore\Block\Adminhtml\Notification\Edit\Tab\CustomCollectionNotification
     * $customCollectionNotification
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Catalog\Model\Product\Type $type
     * @param \Magento\Catalog\Model\Product\Attribute\Source\Status $status
     * @param \Magento\Catalog\Model\Product\Visibility $visibility
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param array $data = []
     */
    public function __construct(
        \Magento\Catalog\Model\Product\Type $type,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Store\Model\WebsiteFactory $websiteFactory,
        \Magento\Catalog\Model\Product\Visibility $visibility,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Catalog\Model\Product\Attribute\Source\Status $status,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory $setsFactory,
        \Webkul\MobikulCore\Block\Adminhtml\Notification\Edit\Tab\CustomCollectionNotification $customCollectionNoti,
        \Magento\Framework\Serialize\Serializer\Serialize $serializer,
        array $data = []
    ) {
        $this->type = $type;
        $this->status = $status;
        $this->visibility = $visibility;
        $this->setsFactory = $setsFactory;
        $this->moduleManager = $moduleManager;
        $this->productFactory = $productFactory;
        $this->websiteFactory = $websiteFactory;
        $this->customCollectionNotification = $customCollectionNoti;
        $this->serializer = $serializer;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setUseAjax(true);
        $this->setId("productGrid");
        $this->setDefaultDir("DESC");
        $this->setDefaultSort("entity_id");
        $this->setSaveParametersInSession(true);
        $this->setVarNameFilter("product_filter");
    }

    /**
     * Function to get store
     *
     * @return int
     */
    protected function _getStore()
    {
        $storeId = (int)$this->getRequest()->getParam("store", 0);
        return $this->_storeManager->getStore($storeId);
    }

    /**
     * Function to prepare collection
     *
     * @return object
     */
    protected function _prepareCollection()
    {
        $store = $this->_getStore();
        $collection = $this->productFactory->create()->getCollection()
            ->addAttributeToSelect("sku")
            ->addAttributeToSelect("name")
            ->addAttributeToSelect("attribute_set_id")
            ->addAttributeToSelect("type_id")
            ->setStore($store);
        if ($this->moduleManager->isEnabled("Magento_CatalogInventory")) {
            $collection->joinField(
                "qty",
                "cataloginventory_stock_item",
                "qty",
                "product_id=entity_id",
                "{{table}}.stock_id=1",
                "left"
            );
        }
        if ($store->getId()) {
            $collection->addStoreFilter($store);
            $collection->joinAttribute(
                "name",
                "catalog_product/name",
                "entity_id",
                null,
                "inner",
                Store::DEFAULT_STORE_ID
            );
            $collection->joinAttribute(
                "custom_name",
                "catalog_product/name",
                "entity_id",
                null,
                "inner",
                $store->getId()
            );
            $collection->joinAttribute("status", "catalog_product/status", "entity_id", null, "inner", $store->getId());
            $collection->joinAttribute(
                "visibility",
                "catalog_product/visibility",
                "entity_id",
                null,
                "inner",
                $store->getId()
            );
            $collection->joinAttribute("price", "catalog_product/price", "entity_id", null, "left", $store->getId());
        } else {
            $collection->addAttributeToSelect("price");
            $collection->joinAttribute("status", "catalog_product/status", "entity_id", null, "inner");
            $collection->joinAttribute("visibility", "catalog_product/visibility", "entity_id", null, "inner");
        }
        $this->setCollection($collection);
        $this->getCollection()->addWebsiteNamesToResult();
        parent::_prepareCollection();
        return $this;
    }

    /**
     * Function to add filter to column
     *
     * @return array
     */
    protected function _addColumnFilterToCollection($column)
    {
        if ($this->getCollection()) {
            if ($column->getId() == "websites") {
                $this->getCollection()->joinField(
                    "websites",
                    "catalog_product_website",
                    "website_id",
                    "product_id=entity_id",
                    null,
                    "left"
                );
            }
            if ($column->getId() == "in_category") {
                $productIds = $this->_getSelectedProducts();
                if (empty($productIds)) {
                    $productIds = 0;
                }
                if ($column->getFilter()->getValue()) {
                    $this->getCollection()->addFieldToFilter("entity_id", ["in"=>$productIds]);
                } elseif (!empty($productIds)) {
                    $this->getCollection()->addFieldToFilter("entity_id", ["nin"=>$productIds]);
                }
            } else {
                parent::_addColumnFilterToCollection($column);
            }
        }
        return $this;
    }

    /**
     * Function to prepare columns
     *
     * @return columns
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            "in_category",
            [
                "type" => "checkbox",
                "name" => "in_category",
                "align" => "center",
                "index" => "entity_id",
                "values" => $this->_getSelectedProducts(),
                "header_css_class" => "a-center"
            ]
        );

        $this->addColumn(
            "entity_id",
            [
                "type" => "number",
                "index" => "entity_id",
                "header" => __("ID"),
                "header_css_class" => "col-id",
                "column_css_class" => "col-id"
            ]
        );

        $this->addColumn(
            "name",
            [
                "index" => "name",
                "class" => "xxx",
                "header" => __("Name")
            ]
        );

        $store = $this->_getStore();
        if ($store->getId()) {
            $this->addColumn(
                "custom_name",
                [
                    "index" => "custom_name",
                    "header" => __("Name in %1", $store->getName()),
                    "header_css_class" => "col-name",
                    "column_css_class" => "col-name"
                ]
            );
        }

        $this->addColumn(
            "sku",
            [
                "index" => "sku",
                "header" => __("SKU")
            ]
        );

        $store = $this->_getStore();
        $this->addColumn(
            "price",
            [
                "type" => "price",
                "index" => "price",
                "header" => __("Price"),
                "currency_code" => $store->getBaseCurrency()->getCode(),
                "header_css_class" => "col-price",
                "column_css_class" => "col-price"
            ]
        );

        $this->addColumn(
            "status",
            [
                "type" => "options",
                "index" => "status",
                "header" => __("Status"),
                "options" => $this->status->getOptionArray()
            ]
        );

        $block = $this->getLayout()->getBlock("grid.bottom.links");
        if ($block) {
            $this->setChild("grid.bottom.links", $block);
        }
        return parent::_prepareColumns();
    }

    /**
     * Function to get grid url
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl("mobikul/notification/getproductgridhtml", ["_current" => true]);
    }

    /**
     * Function to get sekected products
     *
     * @return array
     */
    protected function _getSelectedProducts()
    {
        $productIds = [];
        $notificationData = $this->customCollectionNotification->getNotificationData();
        if (count($notificationData) > 0) {
            $filterData = $this->serializer->unserialize($notificationData["filter_data"]);
            if (is_string($filterData)) {
                $productIds = explode(",", $filterData);
            }
            return $productIds;
        } else {
            return $productIds;
        }
    }
}
