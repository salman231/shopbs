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
namespace Webkul\DeliveryBoy\Model\Deliveryboy;

use Magento\Framework\App\ObjectManager;
use Webkul\DeliveryBoy\Model\ResourceModel\Deliveryboy\CollectionFactory as DeliveryboyResourceCollectionFactory;

class DataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * @var \Magento\Framework\Session\SessionManagerInterface
     */
    protected $session;

    /**
     * @var DeliveryboyResourceCollectionFactory
     */
    protected $collection;

    /**
     * Deliveryboy data object array
     *
     * @var array
     */
    protected $loadedData;

    /**
     * Undocumented function
     *
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param DeliveryboyResourceCollectionFactory $collection
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        string $name,
        string $primaryFieldName,
        string $requestFieldName,
        DeliveryboyResourceCollectionFactory $collection,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $collection->create();
        $this->collection->addFieldToSelect("*");
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    /**
     * @return \Magento\Framework\Session\SessionManagerInterface
     */
    protected function getSession()
    {
        if ($this->session === null) {
            $this->session = ObjectManager::getInstance()
                ->get(\Magento\Framework\Session\SessionManagerInterface::class);
        }
        return $this->session;
    }

    /**
     * Get delivery boy data object array
     *
     * @return array
     */
    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }
        $items = $this->collection->getItems();
        foreach ($items as $item) {
            $result["image"] = $item->getData();
            $this->loadedData[$item->getId()] = $result;
        }
        $data = $this->getSession()->getDeliveryboyFormData();
        if (!empty($data)) {
            $id = isset($data["expressdelivery_deliveryboy"]["id"])
                ? $data["expressdelivery_deliveryboy"]["id"]
                : null;
            $this->loadedData[$id] = $data;
            $this->getSession()->unsImageFormData();
        }
        return $this->loadedData;
    }
}
