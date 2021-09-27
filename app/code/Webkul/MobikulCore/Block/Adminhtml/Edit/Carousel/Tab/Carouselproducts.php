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

namespace Webkul\MobikulCore\Block\Adminhtml\Edit\Carousel\Tab;

use Magento\Backend\Block\Template\Context;

/**
 * Class Carouselproducts block
 */
class Carouselproducts extends \Magento\Backend\Block\Widget\Grid\Extended
{
    protected $type;
    protected $status;
    protected $visibility;
    protected $moduleManager;
    protected $productFactory;

    public function __construct(
        Context $context,
        \Magento\Catalog\Model\Product\Type $type,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Catalog\Model\Product\Visibility $visibility,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Catalog\Model\Product\Attribute\Source\Status $status,
        array $data = []
    ) {
        $this->type = $type;
        $this->status = $status;
        $this->visibility = $visibility;
        $this->moduleManager = $moduleManager;
        $this->productFactory = $productFactory;
        $this->storeManager = $context->getStoreManager();
        parent::__construct($context, $backendHelper, $data);
    }

    protected function _construct()
    {
        parent::_construct();
        $this->setUseAjax(true);
        $this->setDefaultDir("ASC");
        $this->setDefaultSort("entity_id");
        $this->setId("carousel_product_grid");
        $this->setSaveParametersInSession(true);
    }

    protected function _getStore()
    {
        $storeId = (int)$this->getRequest()->getParam("store", 0);
        return $this->storeManager->getStore($storeId);
    }

    protected function _prepareCollection()
    {
        $store = $this->_getStore();
        $collection = $this->productFactory->create()->getCollection()->addAttributeToSelect("*");
        if ($store->getId()) {
            $collection->joinAttribute(
                "visibility",
                "catalog_product/visibility",
                "entity_id",
                null,
                "inner",
                $store->getId()
            );
        } else {
            $collection->joinAttribute("visibility", "catalog_product/visibility", "entity_id", null, "inner");
        }
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
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn(
            "triggers",
            [
                "type" => "checkbox",
                "align" => "center",
                "header" => __("Select"),
                "index" => "id",
                "sortable" => false
            ]
        );
        $this->addColumn(
            "entity_id",
            [
                "type" => "number",
                "align" => "center",
                "width" => "30px",
                "index" => "entity_id",
                "header" => __("ID")
            ]
        );

        $this->addColumn(
            "thumbnail",
            [
                "type" => "image",
                "align" => "center",
                "index" => "thumbnail",
                "header" => __("Thumbnail"),
                "escape" => true,
                "filter" => false,
                "renderer" => \Webkul\MobikulCore\Block\Adminhtml\Edit\Carousel\Tab\Thumbnail::class,
                "sortable" => false
            ]
        );

        $this->addColumn(
            "name",
            [
                "index" => "name",
                "align" => "left",
                "header" => __("Product Name")
            ]
        );

        $this->addColumn(
            "type",
            [
                "index" => "type_id",
                "align" => "left",
                "header" => __("Product Type"),
                "type" => "options",
                "options" => $this->type->getOptionArray()
            ]
        );

        $this->addColumn(
            "status",
            [
                "header" => __("Status"),
                "index" => "status",
                "type" => "options",
                "options" => $this->status->getOptionArray()
            ]
        );
        $this->addColumn(
            "visibility",
            [
                "header" => __("Visibility"),
                "index" => "visibility",
                "type" => "options",
                "options" => $this->visibility->getOptionArray(),
                "header_css_class" => "col-visibility",
                "column_css_class" => "col-visibility"
            ]
        );

        if ($this->moduleManager->isEnabled("Magento_CatalogInventory")) {
            $this->addColumn(
                "qty",
                [
                    "header" => __("Quantity"),
                    "type" => "number",
                    "index" => "qty"
                ]
            );
        }

        return parent::_prepareColumns();
    }

    public function getGridUrl()
    {
        return $this->getUrl("*/*/productGridData", ["_current"=>true]);
    }
}
