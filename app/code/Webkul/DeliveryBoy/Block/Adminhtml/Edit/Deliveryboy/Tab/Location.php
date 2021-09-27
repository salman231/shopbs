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
namespace Webkul\DeliveryBoy\Block\Adminhtml\Edit\Deliveryboy\Tab;

class Location extends \Magento\Backend\Block\Template
{
    /**
     * @var string
     */
    protected $_template = 'deliveryboy/location.phtml';

    /**
     * @var \Webkul\DeliveryBoy\Model\Deliveryboy
     */
    protected $deliveryboy;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Webkul\DeliveryBoy\Model\Deliveryboy $deliveryboy
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Webkul\DeliveryBoy\Model\Deliveryboy $deliveryboy,
        array $data = []
    ) {
        $this->deliveryboy = $deliveryboy;
        $this->_coreRegistry = $registry;

        parent::__construct($context, $data);
    }

    /**
     * @return array
     */
    public function getDeliveryboyLocation()
    {
        $locationData = [];
        $originalRequestData = $this->getRequest()->getParams();
        $deliveryboyId = $originalRequestData["id"] ?? null;
        if ($deliveryboyId) {
            $deliveryboy = $this->deliveryboy->load($deliveryboyId);
            if (!$deliveryboy->getId()) {
                throw new \Magento\Framework\Exception\LocalizedException(__("Invalid Deliveryboy."));
            }
            $locationData["success"] = true;
            $locationData["latitude"] = $deliveryboy->getLatitude();
            $locationData["longitude"] = $deliveryboy->getLongitude();
            $locationData["location"] = $deliveryboy->getAddress();
        }
        return $locationData;
    }
}
