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

namespace Webkul\MobikulCore\Model\Categoryimages;

use Magento\Eav\Model\Config;
use Magento\Framework\App\ObjectManager;
use Webkul\MobikulCore\Model\Categoryimages;
use Magento\Framework\Session\SessionManagerInterface;
use Webkul\MobikulCore\Model\ResourceModel\Categoryimages\Collection;
use Webkul\MobikulCore\Model\ResourceModel\Categoryimages\CollectionFactory as CategoryCollectionFactory;

/**
 * Class DataProvider model
 */
class DataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    protected $session;
    protected $collection;
    protected $loadedData;

    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CategoryCollectionFactory $categoryimagesCollectionFactory,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $categoryimagesCollectionFactory->create();
        $this->collection->addFieldToSelect("*");
    }

    protected function getSession()
    {
        if ($this->session === null) {
            $this->session = ObjectManager::getInstance()
            ->get(\Magento\Framework\Session\SessionManagerInterface::class);
        }
        return $this->session;
    }

    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }
        $items = $this->collection->getItems();
        foreach ($items as $categoryimages) {
            $result["categoryimages"] = $categoryimages->getData();
            $this->loadedData[$categoryimages->getId()] = $result;
        }
        $data = $this->getSession()->getCategoryimagesFormData();
        if (!empty($data)) {
            $categoryimagesId = isset(
                $data["mobikul_categoryimages"]["id"]
            ) ? $data["mobikul_categoryimages"]["id"] : null;
            $this->loadedData[$categoryimagesId] = $data;
            $this->getSession()->unsCategoryimagesFormData();
        }
        return $this->loadedData;
    }
}
