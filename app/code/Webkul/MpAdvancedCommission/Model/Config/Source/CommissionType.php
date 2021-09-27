<?php
/**
 * @category   Webkul
 * @package    Webkul_MpAdvancedCommission
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\MpAdvancedCommission\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Webkul\MpAdvancedCommission\Model\CommissionRules;

class CommissionType implements OptionSourceInterface
{
    /**
     * @var CommissionRules
     */
    protected $_commissionrules;

    /**
     * Constructor
     *
     * @param \Magento\Cms\Model\Page $cmsPage
     */
    public function __construct(CommissionRules $commissionrules)
    {
        $this->_commissionrules = $commissionrules;
    }

    /**
     * Options getter
     * Returns the array of commission type e.g "fixed" and "percent"
     *
     * @return array
     */
    public function toOptionArray()
    {
        $availableOptions = $this->_commissionrules->getAvailableTypes();
        $options = [];
        foreach ($availableOptions as $key => $value) {
            $options[] = [
                'label' => $value,
                'value' => $key,
            ];
        }
        return $options;
    }
}
