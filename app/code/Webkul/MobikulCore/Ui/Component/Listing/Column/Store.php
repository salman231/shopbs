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

namespace Webkul\MobikulCore\Ui\Component\Listing\Column;

class Store extends \Magento\Store\Ui\Component\Listing\Column\Store
{
    protected $request;

    /**
     * Constructor of class
     *
     * @param \Magento\Framework\Escaper                                   $escaper            escaper
     * @param \Magento\Framework\App\Request\Http                          $request            request
     * @param \Magento\Store\Model\System\Store                            $systemStore        systemStore
     * @param \Magento\Framework\View\Element\UiComponent\ContextInterface $context            context
     * @param \Magento\Framework\View\Element\UiComponentFactory           $uiComponentFactory uiComponentFactory
     * @param array                                                        $components         components
     * @param array                                                        $data               data
     */
    public function __construct(
        \Magento\Framework\Escaper $escaper,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Store\Model\System\Store $systemStore,
        \Magento\Framework\View\Element\UiComponent\ContextInterface $context,
        \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    ) {
        $this->request = $request;
        parent::__construct(
            $context,
            $uiComponentFactory,
            $systemStore,
            $escaper,
            $components,
            $data,
            "store_id"
        );
    }

    /**
     * Fucntion to prepareItem
     *
     * @param array $item item
     *
     * @return parent prepareItem
     */
    protected function prepareItem(array $item)
    {
        if ($this->request->getParam("namespace") == "mobikul_notification_list" && is_string($item[$this->storeKey])) {
            $item[$this->storeKey] = explode(",", $item[$this->storeKey]);
        }
        return parent::prepareItem($item);
    }
}
