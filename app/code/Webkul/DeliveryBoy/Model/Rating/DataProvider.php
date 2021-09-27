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
namespace Webkul\DeliveryBoy\Model\Rating;

use Magento\Framework\App\ObjectManager;
use Webkul\DeliveryBoy\Model\ResourceModel\Rating\CollectionFactory as RatingResourceCollectionFactory;

class DataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * @var \Magento\Framework\Session\SessionManagerInterface
     */
    protected $session;
    
    /**
     * @var RatingResourceCollectionFactory
     */
    protected $collection;

    /**
     * Rating data objects array
     *
     * @var array
     */
    protected $loadedData;

    /**
     * @param string                          $name
     * @param string                          $primaryFieldName
     * @param string                          $requestFieldName
     * @param RatingResourceCollectionFactory $ratingCollectionFactory
     * @param array                           $meta
     * @param array                           $data
     */
    public function __construct(
        string $name,
        string $primaryFieldName,
        string $requestFieldName,
        RatingResourceCollectionFactory $ratingCollectionFactory,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $ratingCollectionFactory->create();
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
     * Returns array of rating data object
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
        $data = $this->getSession()->getRatingFormData();
        if (!empty($data)) {
            $id = isset($data["expressdelivery_rating"]["id"]) ? $data["expressdelivery_rating"]["id"] : null;
            $this->loadedData[$id] = $data;
            $this->getSession()->unsImageFormData();
        }
        return $this->loadedData;
    }
}
