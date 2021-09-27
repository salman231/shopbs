<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_DailyDeal
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\DailyDeal\Block;

use Exception;
use Magento\Catalog\Block\Product\AbstractProduct;
use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Widget\Block\BlockInterface;
use Mageplaza\DailyDeal\Helper\Data as HelperData;
use Mageplaza\DailyDeal\Model\DealFactory;

/**
 * Class Deal
 * @package Mageplaza\DailyDeal\Block
 */
class Deal extends AbstractProduct implements BlockInterface
{
    /**
     * @var HelperData
     */
    public $_helperData;

    /**
     * @var DateTime
     */
    protected $_date;

    /**
     * @var DealFactory
     */
    protected $_dealFactory;

    /**
     * @var CategoryFactory
     */
    protected $_categoryFactory;

    /**
     * @var CollectionFactory
     */
    protected $_productCollectionFactory;

    /**
     * Deal constructor.
     *
     * @param Context $context
     * @param HelperData $helperData
     * @param DateTime $date
     * @param DealFactory $dealFactory
     * @param CategoryFactory $categoryFactory
     * @param CollectionFactory $productCollectionFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        HelperData $helperData,
        DateTime $date,
        DealFactory $dealFactory,
        CategoryFactory $categoryFactory,
        CollectionFactory $productCollectionFactory,
        array $data = []
    ) {
        $this->_date                     = $date;
        $this->_helperData               = $helperData;
        $this->_dealFactory              = $dealFactory;
        $this->_categoryFactory          = $categoryFactory;
        $this->_productCollectionFactory = $productCollectionFactory;

        parent::__construct($context, $data);
    }

    /**
     * Get Configurable data
     *
     * @return string|null
     * @throws Exception
     */
    public function getConfigurableData()
    {
        $childIds = $this->_helperData->getChildConfigurableProductIds();
        if (empty($childIds)) {
            return null;
        }

        $params = [
            'dealUrl'                     => $this->getUrl('dailydeal/deal/deal'),
            'childConfigurableProductIds' => $childIds
        ];

        return HelperData::jsonEncode($params);
    }
}
