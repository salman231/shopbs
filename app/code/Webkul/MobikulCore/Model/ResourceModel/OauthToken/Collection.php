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

namespace Webkul\MobikulCore\Model\ResourceModel\OauthToken;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Class Collection model
 */
class Collection extends AbstractCollection
{

    /**
     * @var string
     */
    protected $_idFieldName = "entity_id";

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            \Webkul\MobikulCore\Model\OauthToken::class,
            \Webkul\MobikulCore\Model\ResourceModel\OauthToken::class
        );
    }
}
