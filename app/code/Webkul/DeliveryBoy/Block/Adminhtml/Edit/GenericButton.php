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
namespace Webkul\DeliveryBoy\Block\Adminhtml\Edit;

use Webkul\DeliveryBoy\Controller\RegistryConstants;

class GenericButton
{
    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Backend\Block\Widget\Context $context
     */
    public function __construct(
        \Magento\Framework\Registry $registry,
        \Magento\Backend\Block\Widget\Context $context
    ) {
        $this->registry   = $registry;
        $this->urlBuilder = $context->getUrlBuilder();
    }

    /**
     * Return current deliveryboy id
     *
     * @return int|null
     */
    public function getDeliveryboyId()
    {
        return $this->registry->registry(RegistryConstants::CURRENT_DELIVERYBOY_ID);
    }

    /**
     * Generate url by route and parameters.
     *
     * @param string $route
     * @param array $params
     * @return string
     */
    public function getUrl($route = "", $params = [])
    {
        return $this->urlBuilder->getUrl($route, $params);
    }
}
