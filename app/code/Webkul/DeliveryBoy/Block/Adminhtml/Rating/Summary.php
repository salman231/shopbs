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
namespace Webkul\DeliveryBoy\Block\Adminhtml\Rating;

class Summary extends \Magento\Backend\Block\Template
{
    /**
     * @var string
     */
    protected $_template = "Webkul_DeliveryBoy::rating/summary.phtml";

    /**
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry = null;
    
    /**
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Backend\Block\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Registry $registry,
        \Magento\Backend\Block\Template\Context $context,
        array $data = []
    ) {
        $this->coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * @return float
     */
    public function getRatingSummary()
    {
        return (float)$this->coreRegistry->registry("review_data")->getRating();
    }
}
