<?php
/**
 * Webkul Software.
 *
 *
 *
 * @category  Webkul
 * @package   Webkul_MobikulCore
 * @author    Webkul <support@webkul.com>
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html ASL Licence
 * @link      https://store.webkul.com/license.html
 */

namespace Webkul\MobikulCore\Ui\Component\Listing\Columns;

use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;

class CategoryIconImage extends \Magento\Ui\Component\Listing\Columns\Column
{
    protected $storeManager;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        StoreManagerInterface $storeManager,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->storeManager = $storeManager;
    }

    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource["data"]["items"])) {
            $baseTmpPath = "mobikul/categoryimages/icon/";
            $target = $this->storeManager->getStore()->getBaseUrl(
                \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
            ).$baseTmpPath;
            $fieldName = $this->getData("name");
            foreach ($dataSource["data"]["items"] as &$item) {
                $item[$fieldName."_html"] = "<img src='".$target.$item["icon"]."'/>";
                $item[$fieldName."_src"] = $target.$item["icon"];
            }
        }
        return $dataSource;
    }
}
