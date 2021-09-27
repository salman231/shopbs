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

namespace Webkul\MobikulCore\Model\ResourceModel;

/**
 * Class OauthToken resourceModel
 */
class OauthToken extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * @var string
     */
    protected $_idFieldName = "entity_id";
    
    /**
     * Variable $_date
     *
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $date;
 
    /**
     * Construct
     *
     * @param Magento\Framework\Model\ResourceModel\Db\Context $context        context
     * @param Magento\Framework\Stdlib\DateTime\DateTime       $date           date
     * @param string|null                                      $resourcePrefix resourcePrefix
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        $resourcePrefix = null
    ) {
        parent::__construct($context, $resourcePrefix);
        $this->date = $date;
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init("mobikul_oauth_token", "entity_id");
    }

    /**
     * Select token for a given customer.
     *
     * @param int $customerId customerId
     *
     * @return array|boolean - Row data (array) or false if there is no corresponding row
     */
    public function selectTokenByCustomerId($customerId)
    {
        $connection = $this->getConnection();
        $select = $connection->select()
            ->from($this->getMainTable())
            ->where("customer_id = ?", $customerId);
        return $connection->fetchRow($select);
    }
}
