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

namespace Webkul\MobikulCore\Model\Featuredcategories;

use Magento\Eav\Model\Config;
use Magento\Framework\App\ObjectManager;
use Webkul\MobikulCore\Model\Featuredcategories;
use Magento\Framework\Session\SessionManagerInterface;
use Webkul\MobikulCore\Model\ResourceModel\Featuredcategories\Collection;
use Webkul\MobikulCore\Model\ResourceModel\Featuredcategories\CollectionFactory as FeaturedcatCollectionFactory;

class DataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    protected $session;
    protected $collection;
    protected $loadedData;

    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        FeaturedcatCollectionFactory $featuredcategoriesCollectionFactory,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $featuredcategoriesCollectionFactory->create();
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
        foreach ($items as $featuredcategories) {
            $result["featuredcategories"] = $featuredcategories->getData();
            $this->loadedData[$featuredcategories->getId()] = $result;
        }
        $data = $this->getSession()->getFeaturedcategoriesFormData();
        if (!empty($data)) {
            $featuredcategoriesId = $data["mobikul_featuredcategories"]["id"] ?? null;
            $this->loadedData[$featuredcategoriesId] = $data;
            $this->getSession()->unsFeaturedcategoriesFormData();
        }
        return $this->loadedData;
    }
}
