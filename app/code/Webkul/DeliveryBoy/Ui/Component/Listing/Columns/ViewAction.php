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
namespace Webkul\DeliveryBoy\Ui\Component\Listing\Columns;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class ViewAction extends Column
{
    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource["data"]["items"])) {
            foreach ($dataSource["data"]["items"] as &$item) {
                $name = $this->getData("name");
                if (isset($item["order_id"]) && $item["order_id"] > 0) {
                    $item[$name]["edit"] = [
                        "href"   => $this->urlBuilder->getUrl(
                            "sales/order/view",
                            [
                                "order_id" => $item["order_id"]
                            ]
                        ),
                        "label"  => __("View Order"),
                        "hidden" => false
                    ];
                } else {
                    $item[$name]["edit"] = [
                        "href"  => "#",
                        "label" => "<span>" . __("Offline Order") . "</span>",
                        "hidden" => true
                    ];
                }
            }
        }
        return $dataSource;
    }
}
