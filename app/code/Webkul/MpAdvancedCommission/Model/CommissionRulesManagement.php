<?php
/**
 * @category   Webkul
 * @package    Webkul_MpAdvancedCommission
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\MpAdvancedCommission\Model;

use Webkul\MpAdvancedCommission\Api\CommissionRulesManagementInterface;
use Webkul\MpAdvancedCommission\Model\CommissionRules as Status;
use Webkul\MpAdvancedCommission\Model\ResourceModel\CommissionRules\CollectionFactory;

class CommissionRulesManagement implements CommissionRulesManagementInterface
{
    /**
     * @var CollectionFactory
     */
    protected $_commissionRulesFactory;

    /**
     * @param CollectionFactory $commissionRulesFactory
     */
    public function __construct(CollectionFactory $commissionRulesFactory)
    {
        $this->_commissionRulesFactory = $commissionRulesFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function getRulesCount()
    {
        $commissionRulesData = $this->_commissionRulesFactory->create();
        return $commissionRulesData->getSize();
    }
}
