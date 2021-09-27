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
use Webkul\MobikulCore\Model\OauthTokenFactory;
use Webkul\MobikulCore\Helper\Data;

class CustomerToken extends Column
{
    protected $authTokenFactory;
    private $_helper;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        OauthTokenFactory $authTokenFactory,
        Data $helper,
        array $components = [],
        array $data = []
    ) {
        $this->authTokenFactory = $authTokenFactory;
        $this->_helper = $helper;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Data Source for Customer token
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource["data"]["items"])) {
            foreach ($dataSource["data"]["items"] as &$item) {
                if (isset($item["entity_id"])) {
                    $item[$this->getData("name")] = $this->getCustomerTokenById($item["entity_id"]);
                }
            }
        }
        return $dataSource;
    }

    /**
     * Get Customer Token
     *
     * @param integer $id
     * @return string|null
     */
    private function getCustomerTokenById(int $id)
    {
        $token =  $this->authTokenFactory->create()->load(
            $id,
            "customer_id"
        )->getToken();
        if (!$token) {
            $token = $this->_helper->createCustomerAccessToken($id);
        }
        return $token;
    }
}
