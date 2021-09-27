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

namespace Webkul\MobikulCore\Model\ResourceModel\UserImage;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Class Collection model
 */
class Collection extends AbstractCollection
{
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $loggerInterface,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategyInterface,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Store\Model\StoreManagerInterface $storeManagerInterface,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        parent::__construct(
            $entityFactory,
            $loggerInterface,
            $fetchStrategyInterface,
            $eventManager,
            $connection,
            $resource
        );
    }

    protected function _construct()
    {
        $this->_init(
            \Webkul\MobikulCore\Model\UserImage::class,
            \Webkul\MobikulCore\Model\ResourceModel\UserImage::class
        );
        $this->_map["fields"]["id"] = "main_table.id";
    }
}
