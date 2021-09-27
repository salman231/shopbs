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

use Magento\Framework\UrlInterface;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;

class ViewAction extends Column
{
    protected $_urlBuilder;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    ) {
        $this->_urlBuilder = $urlBuilder;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource["data"]["items"])) {
            foreach ($dataSource["data"]["items"] as &$item) {
                if (isset($item["id"])) {
                    $viewUrlPath = $this->getData("config/viewUrlPath") ?: "#";
                    $urlEntityParamName = $this->getData("config/urlEntityParamName") ?: "id";
                    $item[$this->getData("name")] = [
                        "view" => [
                            "href"  => $this->_urlBuilder->getUrl($viewUrlPath, [$urlEntityParamName=>$item["id"]]),
                            "label" => __("Edit")
                        ]
                    ];
                }
            }
        }
        return $dataSource;
    }
}
